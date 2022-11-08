<?php

namespace Tests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlagHelper;

class FeatureFlagHelperTest extends TestCase
{
    use FeatureFlagHelper;
    use RefreshDatabase;


    public function testCacheSettings()
    {
        Cache::shouldReceive("rememberForever")->twice();

        Cache::shouldReceive("forget")->twice();

        $feature = factory(FeatureFlag::class)->create(
            [
                'key' => 'foo',
                'variants' => ["on"]
            ]
        );

        $this->registerFeatureFlags();

        Auth::shouldReceive('guest')->andReturn(false);

        Auth::shouldReceive('user')->andReturn((object) ["id" => 1]);

        $feature = factory(FeatureFlag::class)->create(
            [
                'key' => 'bar',
                'variants' => ["on"]
            ]
        );
        $this->registerFeatureFlags();
    }

    public function testFormatRemoveQuotes()
    {
        $results = $this->formatVariant('"on"');
        $this->assertEquals("on", $results);

        $results = $this->formatVariant('\'on\'');
        $this->assertEquals("on", $results);
    }
}
