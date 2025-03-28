<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class ProductVariant extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'product_id',
        'size',
        'price',
        'gross_weight',
        'net_weight',
        'tare_weight',
        'standard_weight',
        'free_quantity',
        'packaging',
        'box_dimensions',
        'box_packing',
        'in_stock',
        'is_hidden',
        'is_new'
    ];

    protected $casts = [
        'in_stock' => 'boolean',
        'is_hidden' => 'boolean',
        'is_new' => 'boolean'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('variant_image') ?: null;
    }
}

