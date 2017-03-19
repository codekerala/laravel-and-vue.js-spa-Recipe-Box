<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecipeIngredient extends Model
{
    protected $fillable = [
    	'name', 'qty'
    ];

    public $timestamps = false;

    public static function form()
    {
    	return [
    		'name' => '',
    		'qty' => ''
    	];
    }
}
