<?php

namespace Omadonex\LaravelAcl\Services;

use Illuminate\Support\Carbon;
use Omadonex\LaravelAcl\Classes\ConstantsAcl;
use Omadonex\LaravelAcl\Classes\Exceptions\OmxUserResourceClassNotSetException;
use Omadonex\LaravelAcl\Interfaces\IAcl;
use Omadonex\LaravelAcl\Models\Privilege;
use Omadonex\LaravelAcl\Models\Role;
use Omadonex\LaravelAcl\Traits\AclTrait;
use Omadonex\LaravelSupport\Classes\Exceptions\OmxClassNotUsesTraitException;

class Acl implements IAcl
{
    protected $deepMode;
    protected $user;
    protected $roles;
    protected $privileges;
    protected $userResourceClass;
    protected $isUser;
    protected $isRoot;

    public function __construct($userResourceClass = null, $deepMode = true)
    {
        $this->deepMode = $deepMode;
        $this->user = null;
        $this->roles = collect();
        $this->privileges = collect();
        $this->userResourceClass = $userResourceClass;
        $this->isUser = false;
        $this->isRoot = false;
    }

    public function isDeepMode()
    {
        return $this->deepMode;
    }

    public function getAvailableRoles($privileges = true)
    {
        $relations = ['translates'];
        if ($privileges) {
            $relations[] = 'privileges';
            $relations[] = 'privileges.translates';
        }

        return Role::with($relations)->get();
    }

    public function getAvailablePrivileges()
    {
        return Privilege::with('translates')->get();
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

        $relations = ['roles', 'roles.translates'];
        if ($this->isDeepMode()) {
            $relations[] = 'roles.privileges';
            $relations[] = 'roles.privileges.translates';
            $relations[] = 'privileges';
            $relations[] = 'privileges.translates';
        }
        $user->load($relations);

        $this->roles = $user->roles;

        if ($this->isDeepMode()) {
            foreach ($user->roles as $role) {
                $this->privileges = $this->privileges->concat($role->privileges);
            }
            //Персонально назначенные пользователю привилегии могут иметь срок истечения
            $userPrivileges = $user->privileges->filter(function ($value, $key) {
                //TODO omadonex: проверить корректность проверки даты, учитывая таймзоны
               return is_null($value->expires_at) || ($value->expires_at > Carbon::now()->timestamp);
            });
            $this->privileges = $this->privileges->concat($userPrivileges);
            $this->privileges = $this->privileges->unique->id->values();
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

    public function getPrivileges($onlyNames = false)
    {
        return $onlyNames ? $this->privileges->map->id : $this->privileges;
    }

    public function hasRoles($roles)
    {
        $checkRoles = is_array($roles) ? $roles : [$roles];
        $userRoles = $this->roles->map->id->toArray();

        return !count(array_diff($checkRoles, $userRoles));
    }

    public function check($privileges)
    {
        if ($this->isRoot()) {
            return true;
        }

        $checkPrivileges = is_array($privileges) ? $privileges : [$privileges];
        $userPrivileges = $this->privileges->map->id->toArray();

        return !count(array_diff($checkPrivileges, $userPrivileges));
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

    public function addPrivilege($privilege, $user = null)
    {
        $finalUser = $user ?: $this->user;
        $privileges = array_merge($finalUser->privileges->map->id, $privilege);
        $finalUser->privileges()->sync($privileges);
    }

    public function removeRole($role, $user = null)
    {
        $finalUser = $user ?: $this->user;
        $finalUser->roles()->detach($role);
    }

    public function removePrivilege($privilege, $user = null)
    {
        $finalUser = $user ?: $this->user;
        $finalUser->privileges()->detach($privilege);
    }
}