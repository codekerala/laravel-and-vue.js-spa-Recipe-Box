<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
    	'name', 'description', 'image'
    ];

    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function ingredients()
    {
    	return $this->hasMany(RecipeIngredient::class);
    }

    public function directions()
    {
    	return $this->hasMany(RecipeDirection::class);
    }

    public static function form()
    {
        return [
            'name' => '',
            'image' => '',
            'description' => '',
            'ingredients' => [
                RecipeIngredient::form()
            ],
            'directions' => [
                RecipeDirection::form(),
                RecipeDirection::form()
            ]
        ];
    }
}
