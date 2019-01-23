<?php

namespace Omadonex\LaravelAcl\Traits;

use Omadonex\LaravelAcl\Models\Privilege;
use Omadonex\LaravelAcl\Models\Role;

trait AclTrait
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'acl_pivot_role_user');
    }

    public function privileges()
    {
        return $this->belongsToMany(Privilege::class, 'acl_pivot_privilege_user');
    }
}
