<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Category extends Model 
{
    use HasTranslations;

    protected $fillable = [
        'name_translations',
        'image_url',
    ];

    public $translatable = ['name_translations'];

    protected $casts = [
        'name_translations' => 'array',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    



}