<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Banner extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'is_active', // Banner status (active/inactive)
    ];

    public function registerMediaCollections(): void
    {
        // Banner images collection
        $this->addMediaCollection('banners')
            ->useDisk('public'); // Using 'public' disk
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('webp')
        ->format('webp')
        ->nonQueued()
        ->performOnCollections('banners'); // Perform conversion on 'banners' collection
    }
    
    // Delete the original image after conversion
    protected static function booted()
    {
        static::saved(function ($model) {
            $media = $model->getFirstMedia('banners');
            if ($media) {
                $originalPath = $media->getPath();
                if (file_exists($originalPath)) {
                    unlink($originalPath); // Delete the original image
                }
            }
        });
    }
}
