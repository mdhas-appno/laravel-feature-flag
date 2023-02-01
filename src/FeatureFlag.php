<?php

namespace FriendsOfCat\LaravelFeatureFlags;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $fillable = [
        'key',
        'variants'
    ];

    protected $casts = [
        'variants' => 'json',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            Cache::forget(Feature::FEATURE_FLAG_CACHE_KEY);
        });

        static::deleted(function ($model) {
            Cache::forget(Feature::FEATURE_FLAG_CACHE_KEY);
        });
    }
}
