<?php

namespace Tests\fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlagsEnabler;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlagUserRoleTrait;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;

class UserWithFeatureFlagsEnablerInterface extends Model implements Authenticatable, FeatureFlagsEnabler
{
    use AuthenticableTrait;
    use FeatureFlagUserRoleTrait;

    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password', 'roles', 'teams'];

    public function __construct(array $attributes = [ ])
    {
        parent::__construct($attributes);
    }
}
