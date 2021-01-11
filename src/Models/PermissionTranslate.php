<?php

namespace Omadonex\LaravelAcl\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionTranslate extends Model
{
    protected $table = 'acl_permission_translates';
    protected $fillable = ['model_id', 'lang', 'name', 'description'];
    public $timestamps = false;
}
