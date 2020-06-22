<?php

/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 1/28/16
 * Time: 9:00 PM
 */

namespace FriendsOfCat\LaravelFeatureFlags;

use Illuminate\Support\Facades\Log;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;

trait FeatureFlagHelper
{

    public function registerFeatureFlags()
    {
        try {
            \Cache::rememberForever(\FriendsOfCat\LaravelFeatureFlags\Feature::FEATURE_FLAG_CACHE_KEY, function () {
                $features = FeatureFlag::all()->toArray();

                foreach ($features as $key => $value) {
                    $features = $this->transformFeatures($features, $value, $key);
                    unset($features[$key]);
                }

                return $features;
            });
        } catch (\Exception $e) {
            //Log::info($e->getTraceAsString());
        }
    }

    public function formatVariant($variant)
    {
        return str_replace(["\"", "'"], "", $variant);
    }

    private function transformFeatures($features, $value, $key)
    {
        $features[$value['key']] = $this->getAndSetValue($value);

        if (isset($value['variants']['users'])) {
            $features[$value['key']]['users'] = $value['variants']['users'];
        }

        return $features;
    }

    private function getAndSetValue($value)
    {
        if ($value['variants'] == 'on' or $value['variants'] == 'off') {
            return $value['variants'];
        }

        return $value;
    }
}
