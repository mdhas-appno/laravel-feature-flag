<?php

namespace Tests;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Testing\WithFaker;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlagHelper;
use Tests\fixtures\UserWithFeatureFlagsEnablerInterface;

class FeatureFlagsEnablerTest extends TestCase
{
    use RefreshDatabase, WithFaker, FeatureFlagHelper;

    /**
     * @dataProvider validData
     */
    public function testItWorksWhenInterfaceImplented($userRoles)
    {
        $user = new UserWithFeatureFlagsEnablerInterface([
            'name' => $this->faker->word,
            'email' => $this->faker->email,
            'password' => bcrypt(str_random(25)),
            'roles' => json_encode($userRoles)
        ]);
        $user->save();
        
        $this->be($user);

        factory(FeatureFlag::class)->create(
            [
                'key' => 'testing',
                'variants' => [
                    'roles' => [
                        'developer', 'admin'
                    ]
                ]
            ]
        );

        $this->registerFeatureFlags();

        $this->assertTrue($this->app->get(Gate::class)->allows('feature-flag', 'testing'));
    }

    public function validData()
    {
        return [
                [
                    'admin'
                ],
                [
                    ['admin', 'developer']
                ],
                [
                    'developer'
                ]
        ];
    }
}
