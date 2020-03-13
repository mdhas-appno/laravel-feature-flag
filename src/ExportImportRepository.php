<?php

namespace FriendsOfCat\LaravelFeatureFlags;

use Illuminate\Support\Arr;

class ExportImportRepository
{

    public function export()
    {
        $results = FeatureFlag::select(['key', 'variants'])->get();

        return $results->toArray();
    }

    public function import(array $features)
    {
        FeatureFlag::unguard();
        foreach ($features as $feature) {
            $key = Arr::get($feature, "key");
            FeatureFlag::updateOrCreate(["key" => $key], $feature);
        }
        FeatureFlag::reguard();
    }
}
