<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'product_code',
        'name_translations',
        'description_translations',
        'category_id',
        'country_of_origin',
        'material_property',
        'product_category',
        'weight_unit',
        'barcode'
    ];

    public $translatable = [
        'name_translations',
        'description_translations'
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('Product_image') ?: null;
    }
}
