<?php

namespace Tests;

use FriendsOfCat\LaravelFeatureFlags\FeatureFlag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlagsForJavascript;

class FeatureFlagsForJavascriptTest extends TestCase
{
    use RefreshDatabase;

    public function testGetNoResults()
    {
        $fjs = new FeatureFlagsForJavascript();
        $result = $fjs->get();
        $this->assertEmpty($result);
    }

    public function testGetWithResults()
    {

        factory(FeatureFlag::class)->create([
            'key' => 'testing',
            'variants' => '{ "users": [ "foo@gmail.com", "foo2@gmail.com", "foo3@gmail.com" ] }'
        ]);

        $fjs = new FeatureFlagsForJavascript();
        $result = $fjs->get();
        $this->assertNotEmpty($result);
    }
}
