<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RecipeIngredient;
use App\RecipeDirection;
use App\Recipe;
use App\User;
use File;

class RecipeController extends Controller
{
    public function __construct()
    {
    	$this->middleware('auth:api')
    		->except(['index', 'show']);
    }

    public function index()
    {
    	$recipes = Recipe::orderBy('created_at', 'desc')
    		->get(['id', 'name', 'image']);

    	return response()
    		->json([
    			'recipes' => $recipes
    		]);
    }

    public function create()
    {
        $form = Recipe::form();
    	return response()
    		->json([
    			'form' => $form
    		]);
    }

    public function store(Request $request)
    {
    	$this->validate($request, [
    		'name' => 'required|max:255',
    		'description' => 'required|max:3000',
    		'image' => 'required|image',
    		'ingredients' => 'required|array|min:1',
    		'ingredients.*.name' => 'required|max:255',
    		'ingredients.*.qty' => 'required|max:255',
    		'directions' => 'required|array|min:1',
    		'directions.*.description' => 'required|max:3000'
    	]);

    	$ingredients = [];

        foreach($request->ingredients as $ingredient) {
            $ingredients[] = new RecipeIngredient($ingredient);
        }

	   $directions = [];

        foreach($request->directions as $direction) {
            $directions[] = new RecipeDirection($direction);
        }

    	if(!$request->hasFile('image') && !$request->file('image')->isValid()) {
    		return abort(404, 'Image not uploaded!');
    	}

    	$filename = $this->getFileName($request->image);
    	$request->image->move(base_path('public/images'), $filename);

    	$recipe = new Recipe($request->only('name', 'description'));
    	$recipe->image = $filename;

    	$request->user()->recipes()
    		->save($recipe);

    	$recipe->ingredients()
    		->saveMany($ingredients);

    	$recipe->directions()
    		->saveMany($directions);

    	return response()
    	    ->json([
    	        'saved' => true,
    	        'id' => $recipe->id,
                'message' => 'You have successfully created recipe!'
    	    ]);
    }

    private function getFileName($file)
    {
    	return str_random(32).'.'.$file->extension();
    }

    public function show($id)
    {
        $recipe = Recipe::with(['user', 'ingredients', 'directions'])
            ->findOrFail($id);

        return response()
            ->json([
                'recipe' => $recipe
            ]);
    }


    public function edit($id, Request $request)
    {
        $form = $request->user()->recipes()
            ->with(['ingredients' => function($query) {
                $query->get(['id', 'name', 'qty']);
            }, 'directions' => function($query) {
                $query->get(['id', 'description']);
            }])
            ->findOrFail($id, [
                'id', 'name', 'description', 'image'
            ]);

        return response()
            ->json([
                'form' => $form
            ]);
    }

    public function update($id, Request $request)
    {
        // dd($request->all());
        $this->validate($request, [
            'name' => 'required|max:255',
            'description' => 'required|max:3000',
            'image' => 'image',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.id' => 'integer|exists:recipe_ingredients',
            'ingredients.*.name' => 'required|max:255',
            'ingredients.*.qty' => 'required|max:255',
            'directions' => 'required|array|min:1',
            'directions.*.id' => 'integer|exists:recipe_directions',
            'directions.*.description' => 'required|max:3000'
        ]);

        $recipe = $request->user()->recipes()
            ->findOrFail($id);

        $ingredients = [];
        $ingredientsUpdated = [];

        foreach($request->ingredients as $ingredient) {
            if(isset($ingredient['id'])) {
                RecipeIngredient::where('recipe_id', $recipe->id)
                    ->where('id', $ingredient['id'])
                    ->update($ingredient);

                $ingredientsUpdated[] = $ingredient['id'];
            } else {
                $ingredients[] = new RecipeIngredient($ingredient);
            }
        }

        $directions = [];
        $directionsUpdated = [];

        foreach($request->directions as $direction) {
            if(isset($direction['id'])) {
                RecipeDirection::where('recipe_id', $recipe->id)
                    ->where('id', $direction['id'])
                    ->update($direction);

                $directionsUpdated[] = $direction['id'];
            } else {
                $directions[] = new RecipeDirection($direction);
            }

        }

        $recipe->name = $request->name;
        $recipe->description = $request->description;

        // upload image
        if ($request->hasfile('image') && $request->file('image')->isValid()) {
            $filename = $this->getFileName($request->image);
            $request->image->move(base_path('/public/images'), $filename);

            // remove old image
            File::delete(base_path('/public/images/'.$recipe->image));
            $recipe->image = $filename;
        }

        $recipe->save();

        RecipeIngredient::whereNotIn('id', $ingredientsUpdated)
            ->where('recipe_id', $recipe->id)
            ->delete();

        RecipeDirection::whereNotIn('id', $directionsUpdated)
            ->where('recipe_id', $recipe->id)
            ->delete();

        if(count($ingredients)) {
            $recipe->ingredients()->saveMany($ingredients);
        }

        if(count($directions)) {
            $recipe->directions()->saveMany($directions);
        }

        return response()
            ->json([
                'saved' => true,
                'id' => $recipe->id,
                'message' => 'You have successfully updated recipe!'
            ]);
    }

    public function destroy($id, Request $request)
    {
        $recipe = $request->user()->recipes()
            ->findOrFail($id);

        RecipeIngredient::where('recipe_id', $recipe->id)
            ->delete();

        RecipeDirection::where('recipe_id', $recipe->id)
            ->delete();

        // remove image
        File::delete(base_path('/public/images/'.$recipe->image));

        $recipe->delete();

        return response()
            ->json([
                'deleted' => true
            ]);
    }
}
