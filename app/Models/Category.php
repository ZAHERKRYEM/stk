<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Manipulations;

class Category extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;

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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('categories')
            ->useDisk('public'); 
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->nonQueued();
    }

    protected static function booted()
{
    static::saved(function ($model) {
        //  Get the first media from the 'categories' collection
        $media = $model->getFirstMedia('categories');
        if ($media) {
            $originalPath = $media->getPath(); // Get the original file path
            if (file_exists($originalPath)) {
                unlink($originalPath); //  Delete the original image after conversion
            }
        }
    });
}



}