<?php

namespace Tests;

use FriendsOfCat\LaravelFeatureFlags\FeatureFlagsProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{

    protected function getPackageProviders($app)
    {
        return [
            FeatureFlagsProvider::class,
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/../database/factories');

        $this->app['router']->get('example', function () {
            return view("testing");
        })->name('featured');

        \View::addLocation(__DIR__ . '/../views');

        $this->loadMigrationsFrom(__DIR__ . '/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }


    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');

        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('app.debug', env('APP_DEBUG', true));

        $app['config']->set('laravel-feature-flag.logging', true);

        $app['config']->set('cache.default', 'array');

        $app['config']->set('logging.default', 'single');
        $app['config']->set('logging.channels.single.path', __DIR__ . '/logs/laravel.log');
    }
}
