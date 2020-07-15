<?php

namespace FriendsOfCat\LaravelFeatureFlags;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Feature
{
    const ON = 'on';
    const OFF = 'off';
    const FEATURE_FLAG_CACHE_KEY = 'feature_flags:all';

    /**
     * @var array
     */
    private $instance;

    public function __construct()
    {
        $this->instance = Cache::get(Feature::FEATURE_FLAG_CACHE_KEY, []);
    }

    /**
     * Check if a feature flag is enabled.
     *
     * @param string $featureKey
     * @param mixed $variant (optional)
     * @param \Illuminate\Contracts\Auth\Access\Authorizable $user (optional)
     * @return bool
     */
    public function isEnabled($featureKey, $variant = null, $user = null)
    {
        if (! $variant) {
            $variant = $this->getConfig($featureKey);
        }

        if ($variant != self::ON and $variant != self::OFF) {
            return $this->isUserEnabled($variant, $user);
        }

        return $variant == self::ON;
    }

    /**
     * @param string $featureKey
     * @return mixed
     */
    public function getConfig($featureKey)
    {
        if (isset($this->instance[$featureKey])) {
            return $this->instance[$featureKey];
        }

        $featureFlag = FeatureFlag::where('key', $featureKey)->first();

        if (isset($featureFlag)) {
            return  $featureFlag->variants;
        }

        return self::OFF;
    }

    /**
     * @param $feature_variant
     * @param \Illuminate\Contracts\Auth\Access\Authorizable $user (optional)
     * @return bool
     */
    protected function isUserEnabled($feature_variant, $user = null)
    {
        if ($user_email = $this->getUserEmail($user)) {
            if (empty($feature_variant['users'])) {
                return false;
            }

            return in_array(
                strtolower($user_email),
                array_map('strtolower', $feature_variant['users']),
                true
            );
        }

        return false;
    }


    /**
     * @param \Illuminate\Contracts\Auth\Access\Authorizable $user (optional)
     * @return string
     */
    public function getUserEmail($user)
    {
        if ($user && $user->email) {
            return $user->email;
        }

        if (Auth::guest()) {
            return false;
        }

        return Auth::user()->email;
    }
}
