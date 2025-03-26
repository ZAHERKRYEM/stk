<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Banner extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'is_active',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banners')
            ->useDisk('public');
    }
}
