<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 1/24/16
 * Time: 11:06 AM
 */

namespace FriendsOfCat\LaravelFeatureFlags;

use Illuminate\Support\Facades\Auth;

class Feature
{
    const ON  = "on";
    const OFF = "off";
    const FEATURE_FLAG_CACHE_KEY = "feature_flags:all";

    /**
     * @var array
     */
    private $instance;

    /**
     * @var array
     */
    private $stanza;

    /**
     * @param array $stanza
     */
    public function __construct()
    {
        $this->instance = \Cache::get(Feature::FEATURE_FLAG_CACHE_KEY, []);
    }

    /**
     * @param $feature
     * @return bool
     */
    public function isEnabled($feature)
    {
        $feature_variant = $this->getConfig($feature);

        if ($feature_variant != self::ON and $feature_variant != self::OFF) {
            return $this->isUserEnabled($feature_variant);
        }

        return ($feature_variant == self::ON);
    }

    /**
     * @param $feature
     * @return string
     */
    private function getConfig($feature)
    {
        if (isset($this->instance->stanza[$feature])) {
            return $this->instance->stanza[$feature];
        }

        $feature_flag = FeatureFlag::where('key', $feature)->first();
        if (isset($feature_flag)) {
            return  $feature_flag->variants;
        }

        return self::OFF;
    }

    /**
     * @param $feature_variant
     * @return bool
     */
    protected function isUserEnabled($feature_variant)
    {
        if ($user_email = $this->getUserEmail()) {
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
     * @param string|int $userId
     * @return string
     */
    public function getUserEmail()
    {
        if (Auth::guest()) {
            return false;
        }

        return Auth::user()->email;
    }
}
