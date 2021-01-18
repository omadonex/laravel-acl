<?php

namespace Omadonex\LaravelAcl\Models;

use Illuminate\Database\Eloquent\Model;
use Omadonex\LaravelSupport\Classes\ConstCustom;
use Omadonex\LaravelSupport\Traits\ProtectedGenerateTrait;

class RoleTranslate extends Model
{
    use ProtectedGenerateTrait;

    protected $table = 'acl_role_translates';
    protected $fillable = ['model_id', 'lang', 'name', 'description'];
    public $timestamps = false;

    protected $casts = [
        ConstCustom::DB_FIELD_PROTECTED_GENERATE => 'boolean',
    ];
}
