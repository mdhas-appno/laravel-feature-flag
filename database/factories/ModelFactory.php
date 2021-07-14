<?php

use Illuminate\Support\Str;

$factory->define(\FriendsOfCat\LaravelFeatureFlags\FeatureFlag::class, function ($faker) {
    return [
        'key' => Str::random(3),
        'variants' => []
    ];
});



$factory->define(\FriendsOfCat\LaravelFeatureFlags\FeatureFlagUser::class, function ($faker) {
    return [
        'name' => $faker->word,
        'email' => $faker->email,
        'password' => bcrypt(Str::random(25)),
        'roles' => "['admin']"
    ];
});
