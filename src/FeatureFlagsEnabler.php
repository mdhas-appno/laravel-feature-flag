<?php

namespace FriendsOfCat\LaravelFeatureFlags;

interface FeatureFlagsEnabler
{
    public function getFieldValueForFeatureFlags(string $fieldName): ?array;
}
