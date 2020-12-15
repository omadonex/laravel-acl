<?php

namespace Omadonex\LaravelAcl\Models;

use Illuminate\Database\Eloquent\Model;
use Omadonex\LaravelLocale\Traits\TranslateTrait;

class Privilege extends Model
{
    use TranslateTrait;

    protected $table = 'acl_privileges';
    protected $fillable = [];
    public $incrementing = false;
    public $timestamps = false;

    public $availableRelations = ['translates', 'roles', 'group'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'acl_pivot_privilege_role');
    }

    public function group()
    {
        return $this->belongsTo(PrivilegeGroup::class, 'privilege_group_id');
    }
}
