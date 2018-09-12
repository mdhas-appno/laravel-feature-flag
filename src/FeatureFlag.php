<?php

namespace FriendsOfCat\LaravelFeatureFlags;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $casts = [
        'variants' => 'json',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            \Cache::forget(\FriendsOfCat\LaravelFeatureFlags\Feature::FEATURE_FLAG_CACHE_KEY);
        });
    }
}
