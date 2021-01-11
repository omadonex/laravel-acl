<?php

namespace Omadonex\LaravelAcl\Traits;

use Omadonex\LaravelAcl\Models\Permission;
use Omadonex\LaravelAcl\Models\Role;

trait AclTrait
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'acl_pivot_role_user');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'acl_pivot_permission_user');
    }
}
