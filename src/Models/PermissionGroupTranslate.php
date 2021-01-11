<?php

namespace Omadonex\LaravelAcl\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionGroupTranslate extends Model
{
    protected $table = 'acl_permission_group_translates';
    protected $fillable = ['model_id', 'lang', 'name', 'description'];
    public $timestamps = false;
}
