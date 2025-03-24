<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class Product extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'product_code',
        'name_translations',
        'description_translations',
        'price',
        'category_id',
        'image_url',
        'gallery',
        'sizes',
        'country_of_origin',
        'material_property',
        'product_category',
        'gross_weight',
        'net_weight',
        'tare_weight',
        'standard_weight',
        'free_quantity',
        'weight_unit',
        'packaging',
        'supplier_name',
        'box_gross_weight',
        'box_dimensions',
        'box_packing',
        'in_stock',
        'is_hidden',
        'is_new',
    ];

    public $translatable = ['name_translations', 'description_translations'];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
        'gallery' => 'array',
        'sizes' => 'array',
        'in_stock' => 'boolean',
        'is_hidden' => 'boolean',
        'is_new' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('public') 
            ->singleFile();

        $this->addMediaCollection('gallery')
            ->useDisk('public'); 
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->nonQueued();
    }
}