<?php


$factory->define(\FriendsOfCat\LaravelFeatureFlags\FeatureFlag::class, function ($faker) {
    return [
        'key' => str_random(3),
        'usage_count' => $faker->randomDigit,
        'variants' => []
    ];
});


$factory->define(\FriendsOfCat\LaravelFeatureFlags\FeatureFlagUser::class, function ($faker) {
    return [
        'name' => $faker->word,
        'email' => $faker->email,
        'password' => bcrypt(str_random(25))
    ];
});
