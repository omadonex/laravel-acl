<?php

namespace Omadonex\LaravelAcl\Models;

use Illuminate\Database\Eloquent\Model;
use Omadonex\LaravelLocale\Traits\TranslateTrait;

class Permission extends Model
{
    use TranslateTrait;

    protected $table = 'acl_permissions';
    protected $fillable = [];
    public $incrementing = false;
    public $timestamps = false;

    public $availableRelations = ['translates', 'roles', 'group'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'acl_pivot_permission_role');
    }

    public function group()
    {
        return $this->belongsTo(PermissionGroup::class, 'permission_group_id');
    }
}
