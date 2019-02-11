<?php

namespace Omadonex\LaravelAcl\Models;

use Illuminate\Database\Eloquent\Model;

class PrivilegeGroupTranslate extends Model
{
    protected $table = 'acl_privilege_group_translates';
    protected $fillable = ['model_id', 'lang', 'name', 'description'];
    public $timestamps = false;
}
