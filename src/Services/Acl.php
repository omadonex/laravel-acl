<?php

namespace Omadonex\LaravelAcl\Services;

use Illuminate\Support\Carbon;
use Omadonex\LaravelAcl\Classes\ConstantsAcl;
use Omadonex\LaravelAcl\Classes\Exceptions\OmxUserResourceClassNotSetException;
use Omadonex\LaravelAcl\Interfaces\IAcl;
use Omadonex\LaravelAcl\Models\Permission;
use Omadonex\LaravelAcl\Models\Role;
use Omadonex\LaravelAcl\Traits\AclTrait;
use Omadonex\LaravelSupport\Classes\Exceptions\OmxClassNotUsesTraitException;

class Acl implements IAcl
{
    protected $deepMode;
    protected $user;
    protected $roles;
    protected $permissions;
    protected $userResourceClass;
    protected $isUser;
    protected $isRoot;
    protected $additionalRelations;

    public function __construct($userResourceClass = null, $deepMode = true)
    {
        $this->deepMode = $deepMode;
        $this->user = null;
        $this->roles = collect();
        $this->permissions = collect();
        $this->userResourceClass = $userResourceClass;
        $this->isUser = false;
        $this->isRoot = false;
        $this->additionalRelations = [];
    }

    public function isDeepMode()
    {
        return $this->deepMode;
    }

    public function getAvailableRoles($permissions = true)
    {
        $relations = ['translates'];
        if ($permissions) {
            $relations[] = 'permissions';
            $relations[] = 'permissions.translates';
        }

        return Role::with($relations)->get();
    }

    public function getAvailablePermissions()
    {
        return Permission::with('translates')->get();
    }

    public function loggedIn()
    {
        return !is_null($this->user);
    }

    /**
     * @param $user
     * @throws OmxClassNotUsesTraitException
     */
    public function setUser($user)
    {
        $class = get_class($user);
        if (!in_array(AclTrait::class, class_uses($class))) {
            throw new OmxClassNotUsesTraitException($class, AclTrait::class);
        }

        $this->user = $user;

        $relations = array_merge($this->additionalRelations, ['roles', 'roles.translates']);
        if ($this->isDeepMode()) {
            $relations[] = 'roles.permissions';
            $relations[] = 'roles.permissions.translates';
            $relations[] = 'permissions';
            $relations[] = 'permissions.translates';
        }
        $user->load($relations);

        $this->roles = $user->roles;

        if ($this->isDeepMode()) {
            foreach ($user->roles as $role) {
                $this->permissions = $this->permissions->concat($role->permissions);
            }
            //Персонально назначенные пользователю привилегии могут иметь срок истечения
            $userPermissions = $user->permissions->filter(function ($value, $key) {
                //TODO omadonex: проверить корректность проверки даты, учитывая таймзоны
                $nowTs = Carbon::now()->timestamp;
                return is_null($value->expires_at) || (($value->expires_at > $nowTs) && ($value->starting_at < $nowTs));
            });
            $this->permissions = $this->permissions->concat($userPermissions);
            $this->permissions = $this->permissions->unique->id->values();
        }

        if (!$this->roles->count()) {
            $this->roles->push(Role::with('translates')->find(ConstantsAcl::ROLE_USER));
            $this->isUser = true;
        } else {
            $this->isRoot = $this->hasRoles(ConstantsAcl::ROLE_ROOT);
        }
    }

    /**
     * @param bool $resource
     * @param null $resourceClass
     * @return null|string
     * @throws OmxUserResourceClassNotSetException
     */
    public function getUser($resource = false, $resourceClass = null)
    {
        if (!$this->user) {
            return null;
        }

        if (!$resource) {
            return $this->user;
        }

        $finalResourceClass = $this->userResourceClass;
        if ($resourceClass) {
            $finalResourceClass = $resourceClass;
        }

        if (!$finalResourceClass) {
            throw new OmxUserResourceClassNotSetException;
        }

        $res = new $finalResourceClass($this->user);

        return json_encode($res->toResponse(app('request'))->getData()->data);
    }

    public function getRoles($onlyNames = false)
    {
        return $onlyNames ? $this->roles->map->id : $this->roles;
    }

    public function getPermissions($onlyNames = false)
    {
        return $onlyNames ? $this->permissions->map->id : $this->permissions;
    }

    public function hasRoles($roles)
    {
        $checkRoles = is_array($roles) ? $roles : [$roles];
        $userRoles = $this->roles->map->id->toArray();

        return !count(array_diff($checkRoles, $userRoles));
    }

    public function check($permissions)
    {
        if ($this->isRoot()) {
            return true;
        }

        $checkPermissions = is_array($permissions) ? $permissions : [$permissions];
        $userPermissions = $this->permissions->map->id->toArray();

        return !count(array_diff($checkPermissions, $userPermissions));
    }

    public function checkRoles($rolesCombined, $rootStrict = false)
    {
        if (!$rootStrict && $this->isRoot()) {
            return true;
        }

        if (is_array($rolesCombined)) {
            foreach ($rolesCombined as $roles) {
                if ($this->hasRoles($roles)) {
                    return true;
                }
            }

            return false;
        }

        return $this->hasRoles($rolesCombined);
    }

    public function isRoot()
    {
        return $this->isRoot;
    }

    public function isUser()
    {
        return $this->isUser;
    }

    public function addRole($role, $user = null)
    {
        $finalUser = $user ?: $this->user;
        $roles = array_merge($finalUser->roles->map->id, $role);
        $finalUser->roles()->sync($roles);
    }

    public function addPermission($permission, $user = null)
    {
        $finalUser = $user ?: $this->user;
        $permissions = array_merge($finalUser->permissions->map->id, $permission);
        $finalUser->permissions()->sync($permissions);
    }

    public function removeRole($role, $user = null)
    {
        $finalUser = $user ?: $this->user;
        $finalUser->roles()->detach($role);
    }

    public function removePermission($permission, $user = null)
    {
        $finalUser = $user ?: $this->user;
        $finalUser->permissions()->detach($permission);
    }
}