<?php

namespace Omadonex\LaravelAcl\Models;

use Illuminate\Database\Eloquent\Model;
use Omadonex\LaravelSupport\Classes\ConstantsCustom;
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
        ConstantsCustom::DB_FIELD_PROTECTED_GENERATE => 'boolean',
    ];

    public $availableRelations = ['translates', 'privileges'];

    public function privileges()
    {
        return $this->belongsToMany(Privilege::class, 'acl_pivot_privilege_role');
    }
}
