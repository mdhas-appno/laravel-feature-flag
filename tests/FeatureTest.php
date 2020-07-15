<?php

namespace Tests;

use Mockery;
use Illuminate\Support\Facades\DB;
use FriendsOfCat\LaravelFeatureFlags\Feature;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlag;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlagUser;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlagHelper;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @coversDefaultClass \FriendsOfCat\LaravelFeatureFlags\Feature
 */
class FeatureTest extends TestCase
{
    use DatabaseTransactions, FeatureFlagHelper;

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
    public function testIsEnabledPassingKeyAndUser()
    {
        $user = factory(FeatureFlagUser::class)->create();

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

        $this->assertTrue((new Feature)->isEnabled('feature_1', null, $user));
        $this->assertFalse((new Feature)->isEnabled('feature_2', null, $user));
        $this->assertTrue((new Feature)->isEnabled('feature_3', null, $user));
        $this->assertFalse((new Feature)->isEnabled('feature_4', null, $user));
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

    /**
     * @test
     * @covers ::getConfig
     */
    public function testGetConfigFromCache()
    {
        [$feature1, $feature2] = factory(FeatureFlag::class)->times(2)->create([
            'variants' => 'on',
        ]);

        DB::enableQueryLog();

        $feature = new Feature;
        $this->assertEquals('on', $feature->getConfig($feature1->key));
        $this->assertEquals('on', $feature->getConfig($feature2->key));
        $this->assertCount(2, DB::getQueryLog(), 'It should make 2 queries to get above results');

        // this will save the feature flags in the cache
        $this->registerFeatureFlags();

        $currentQueryCount = count(DB::getQueryLog());
        $feature = new Feature(true);
        $this->assertEquals('on', $feature->getConfig($feature1->key));
        $this->assertEquals('on', $feature->getConfig($feature2->key));
        $this->assertCount($currentQueryCount, DB::getQueryLog(), 'It should NOT make any more queries since its cached');
    }
}
