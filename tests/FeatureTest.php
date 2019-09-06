<?php

namespace Tests;

use FriendsOfCat\LaravelFeatureFlags\Feature;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlag;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlagUser;

/**
 * @coversDefaultClass \FriendsOfCat\LaravelFeatureFlags\Feature
 */
class FeatureTest extends TestCase
{
    /**
     * @test
     * @covers ::isEnabled
     */
    public function testIsEnabledPassingKey()
    {
        $user = factory(FeatureFlagUser::class)->create();
        $this->be($user);

        factory(FeatureFlag::class)->create([
            'key' => 'feature_1',
            'variants' => 'on'
        ]);

        factory(FeatureFlag::class)->create([
            'key' => 'feature_2',
            'variants' => 'off'
        ]);

        factory(FeatureFlag::class)->create([
            'key' => 'feature_3',
            'variants' => ['users' => [$user->email]],
        ]);

        factory(FeatureFlag::class)->create([
            'key' => 'feature_3',
            'variants' => ['users' => []],
        ]);

        $this->assertTrue((new Feature)->isEnabled('feature_1'));
        $this->assertFalse((new Feature)->isEnabled('feature_2'));
        $this->assertTrue((new Feature)->isEnabled('feature_3'));
        $this->assertFalse((new Feature)->isEnabled('feature_4'));
    }

    /**
     * @test
     * @covers ::isEnabled
     */
    public function testIsEnabledPassingVariant()
    {
        $user = factory(FeatureFlagUser::class)->create();
        $this->be($user);

        $feature1 = factory(FeatureFlag::class)->create([
            'key' => 'feature_1',
            'variants' => 'on'
        ]);

        $feature2 = factory(FeatureFlag::class)->create([
            'key' => 'feature_2',
            'variants' => 'off'
        ]);

        $feature3 = factory(FeatureFlag::class)->create([
            'key' => 'feature_3',
            'variants' => ['users' => [$user->email]],
        ]);

        $this->assertTrue((new Feature)->isEnabled('feature_1', $feature1->variants));
        $this->assertFalse((new Feature)->isEnabled('feature_2', $feature2->variants));
        $this->assertTrue((new Feature)->isEnabled('feature_3', $feature3->variants));
    }
}
