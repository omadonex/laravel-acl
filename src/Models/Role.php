<?php

namespace Omadonex\LaravelAcl\Models;

use Illuminate\Database\Eloquent\Model;
use Omadonex\LaravelSupport\Classes\ConstCustom;
use Omadonex\LaravelSupport\Traits\ProtectedGenerateTrait;
use Omadonex\LaravelLocale\Traits\TranslateTrait;

class Role extends Model
{
    use TranslateTrait, ProtectedGenerateTrait;

    protected $table = 'acl_roles';
    protected $fillable = ['is_root', 'is_staff'];
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        ConstCustom::DB_FIELD_PROTECTED_GENERATE => 'boolean',
    ];

    public $availableRelations = ['translates', 'permissions'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'acl_pivot_permission_role');
    }
}
