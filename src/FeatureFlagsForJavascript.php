<?php

namespace FriendsOfCat\LaravelFeatureFlags;

use Illuminate\Support\Facades\Cache;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature as FeatureFacade;

class FeatureFlagsForJavascript
{
    use FeatureFlagHelper;

    /**
     * @return array
     */
    public static function get()
    {
        if (! Cache::has(Feature::FEATURE_FLAG_CACHE_KEY)) {
            (new self)->registerFeatureFlags();
        }

        $flags = Cache::get(Feature::FEATURE_FLAG_CACHE_KEY);

        return collect($flags)->map(function ($variant, $key) {
            return FeatureFacade::isEnabled($key, $variant);
        })->toArray();
    }
}
