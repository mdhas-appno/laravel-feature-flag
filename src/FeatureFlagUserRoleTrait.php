<?php

namespace FriendsOfCat\LaravelFeatureFlags;

trait FeatureFlagUserRoleTrait
{
    public function getFieldValueForFeatureFlags(string $fieldName): ?array
    {
        return (array) json_decode($this->$fieldName, true);
    }
}
