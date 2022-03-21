<?php

namespace FriendsOfCat\LaravelFeatureFlags;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlagsEnabler;

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
    * Check if a feature flag exists.
    *
    * @param string $featureKey
    * @param mixed $variant (optional)
    * @return bool
    */
    public function exists($featureKey)
    {
        return isset($this->instance[$featureKey]);
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
            return $this->isUserEnabled($variant, $user)
                || $this->isRoleEnabled($variant, $user)
                || $this->isTeamEnabled($variant, $user);
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

    private function getUserRoles($user)
    {
        return ($user && $user->roles) ? $user->roles : false;
    }

    private function getUserTeams($user)
    {
        if ($user && $user->teams instanceof Collection) {
            return $user->teams->pluck('name')->toArray();
        }

        return ($user && $user->teams)
            ? $user->teams
            : false;
    }

    public function isRoleEnabled($feature_variant, $user = null)
    {
        $fieldName = 'roles';

        if (empty($feature_variant[$fieldName])) {
            return false;
        }

        $user = $user ?? Auth::user();
        if ($user_roles =
            ($user instanceof FeatureFlagsEnabler)
                ? $user->getFieldValueForFeatureFlags($fieldName)
                : $this->getUserRoles($user)
        ) {
            $filtered = Arr::where(
                array_map('strtolower', $user_roles),
                function ($value, $key) use ($feature_variant, $fieldName) {
                    return in_array($value, $feature_variant[$fieldName], true);
                }
            );

            return ! empty($filtered);
        }

        return false;
    }

    public function isTeamEnabled($feature_variant, $user = null)
    {
        $fieldName = 'teams';

        if (empty($feature_variant[$fieldName])) {
            return false;
        }

        $user = $user ?? Auth::user();

        if ($user_teams=
            ($user instanceof FeatureFlagsEnabler)
                ? $user->getFieldValueForFeatureFlags($fieldName)
                : $this->getUserTeams($user)
        ) {
            $filtered = Arr::where(
                $user_teams,
                function ($value, $key) use ($feature_variant, $fieldName) {
                    return in_array($value, $feature_variant[$fieldName]);
                }
            );

            return ! empty($filtered);
        }

        return false;
    }
}
