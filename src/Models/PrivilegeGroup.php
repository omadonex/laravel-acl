<?php

namespace Omadonex\LaravelAcl\Models;

use Illuminate\Database\Eloquent\Model;
use Omadonex\LaravelSupport\Traits\TranslateTrait;

class PrivilegeGroup extends Model
{
    use TranslateTrait;

    protected $table = 'acl_privilege_groups';
    protected $fillable = [];
    public $incrementing = false;
    public $timestamps = false;

    public $availableRelations = ['translates', 'privileges'];

    public function privileges()
    {
        return $this->hasMany(Privilege::class);
    }
}
