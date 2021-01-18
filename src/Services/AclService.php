<?php

namespace Omadonex\LaravelAcl\Services;

use App\Models\User;
use Illuminate\Routing\Route;
use Illuminate\Support\Carbon;
use Omadonex\LaravelAcl\Classes\ConstAcl;
use Omadonex\LaravelAcl\Classes\Exceptions\OmxUserResourceClassNotSetException;
use Omadonex\LaravelAcl\Interfaces\IAclService;
use Omadonex\LaravelAcl\Models\Permission;
use Omadonex\LaravelAcl\Models\Role;

class AclService implements IAclService
{
    protected $mode;
    protected $deepMode;
    protected $routeMap;

    protected $roleList;
    protected $permissionList;

    protected $user;
    protected $userResourceClass;

    protected $relationList;

    /**
     * AclService constructor.
     * @param array $routeMap
     * @param null $userResourceClass
     * @param bool $deepMode
     * @param string $mode
     */
    public function __construct(array $routeMap, $userResourceClass = null, bool $deepMode = true, string $mode = self::MODE_DENY)
    {
        $this->mode = $mode;
        $this->deepMode = $deepMode;
        $this->routeMap = $routeMap;
        
        $this->roleList = collect();
        $this->permissionList = collect();
        
        $this->user = null;
        $this->userResourceClass = $userResourceClass;
        
        $this->relationList = [];
    }

    /**
     * @param $role
     * @param User|null $user
     */
    public function addRole($role, User $user = null): void
    {
        $finalUser = $user ?: $this->user;
        $roles = array_merge($finalUser->roles->map->id, $role);
        $finalUser->roles()->sync($roles);
    }

    /**
     * @param $permission
     * @param User|null $user
     */
    public function addPermission($permission, User $user = null): void
    {
        $finalUser = $user ?: $this->user;
        $permissions = array_merge($finalUser->permissions->map->id, $permission);
        $finalUser->permissions()->sync($permissions);
    }

    /**
     * @param $permissions
     * @param string $type
     * @param bool $strict
     * @return bool
     */
    public function check($permissions, string $type = self::CHECK_TYPE_AND, bool $strict = false): bool
    {
        //Пользователь ROOT - ему доступно все
        if (!$strict && $this->isRoot()) {
            return true;
        }

        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        if ($type === self::CHECK_TYPE_AND) {
            return !array_diff($permissions, $this->permissionList->toArray());
        }

        if ($type === self::CHECK_TYPE_OR) {
            return (bool)array_intersect($permissions, $this->permissionList->toArray());
        }

        return false;
    }

    /**
     * @param $permissions
     * @param User $user
     * @param string $type
     * @param bool $strict
     * @return bool
     */
    public function checkForUser($permissions, User $user, string $type = self::CHECK_TYPE_AND, bool $strict = false): bool
    {
        return $this->runForUser($user, 'check', [$permissions, $type, $strict]);
    }

    /**
     * @param $roles
     * @param string $type
     * @param bool $strict
     * @return bool
     */
    public function checkRole($roles, string $type = self::CHECK_TYPE_AND, bool $strict = false): bool
    {
        //Пользователь ROOT - ему доступно все
        if (!$strict && $this->isRoot()) {
            return true;
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if ($type === self::CHECK_TYPE_AND) {
            return !array_diff($roles, $this->roleList->toArray());
        }

        if ($type === self::CHECK_TYPE_OR) {
            return (bool)array_intersect($roles, $this->roleList->toArray());
        }

        return false;
    }

    /**
     * @param $roles
     * @param User $user
     * @param string $type
     * @param bool $strict
     * @return bool
     */
    public function checkRoleForUser($roles, User $user, string $type = self::CHECK_TYPE_AND, bool $strict = false): bool
    {
        return $this->runForUser($user, 'checkRole', [$roles, $type, $strict]);
    }

    /**
     * @param string $routeName
     * @param bool $strict
     * @return bool
     */
    public function checkRoute(string $routeName, bool $strict = false): bool
    {
        //Пользователь ROOT - ему доступно все
        if (!$strict && $this->isRoot()) {
            return true;
        }

        //Ищем в группе запрещенных для всех роутов
        $permission = $this->searchInSection($routeName, self::SECTION_DENIED);
        if ($permission === true) {
            return false;
        }

        //Ищем в группе разрешенных для всех роутов
        $permission = $this->searchInSection($routeName, self::SECTION_ALLOWED);
        if ($permission === true) {
            return true;
        }

        //Ищем в группе роутов с разрешениями
        $permission = $this->searchInSection($routeName, self::SECTION_PROTECTED);
        //Если не нашли (роут не указан нигде, значит действуем по настройке режима Acl)
        if ($permission === false) {
            return $this->mode === self::MODE_ALLOW;
        }

        //Проверяем либо группу разрешений, либо одно разрешение
        if (is_array($permission)) {
            $type = $permission['type'] ?? self::CHECK_TYPE_AND;
            $permission = $permission['permissions'];
        } else {
            $type = self::CHECK_TYPE_AND;
        }

        return $this->check($permission, $type);
    }

    /**
     * @param string $routeName
     * @param User $user
     * @param bool $strict
     * @return bool
     */
    public function checkRouteForUser(string $routeName, User $user, bool $strict = false): bool
    {
        return $this->runForUser($user, 'checkRoute', [$routeName, $strict]);
    }

    /**
     * @return array
     */
    public function getAllPermissionList(): array
    {
        return Permission::with('translates')->get()->toArray();
    }

    /**
     * @param bool $permissions
     * @return array
     */
    public function getAllRoleList(bool $permissions = true): array
    {
        $relations = ['translates'];
        if ($permissions) {
            $relations[] = 'permissions';
            $relations[] = 'permissions.translates';
        }

        return Role::with($relations)->get()->toArray();
    }

    /**
     * @param bool $onlyNames
     * @return array
     */
    public function getPermissionList(bool $onlyNames = false): array
    {
        return $onlyNames ? $this->permissionList->map->id : $this->permissionList->toArray();
    }

    /**
     * @param bool $onlyNames
     * @return array
     */
    public function getRoleList(bool $onlyNames = false): array
    {
        return $onlyNames ? $this->roleList->map->id : $this->roleList->toArray();
    }

    /**
     * @return array
     */
    public function getRoutesData(): array
    {
        /** @var Route $route */
        $routes = Route::getRoutes()->getRoutes();
        $routesData = [];
        foreach ($routes as $route) {
            $routeName = $route->getName();
            $routePath = $route->getPath();
            $routeMethods = $route->getMethods();

            //Ищем в группе запрещенных для всех роутов
            if ($this->searchInSection($routeName, self::MODE_DENY) === true) {
                $routesData[self::MODE_DENY][] = ['name' => $routeName, 'path' => $routePath, 'methods' => $routeMethods];
                continue;
            }

            //Ищем в группе разрешенных для всех роутов
            if ($this->searchInSection($routeName, self::MODE_ALLOW) === true) {
                $routesData[self::MODE_ALLOW][] = ['name' => $routeName, 'path' => $routePath, 'methods' => $routeMethods];
                continue;
            }

            //Ищем в группе роутов с разрешениями
            $permission = $this->searchInSection($routeName, self::SECTION_PROTECTED);
            if ($permission === false) {
                $routesData['free'][] = ['name' => $routeName, 'path' => $routePath, 'action' => $route->getActionName(), 'methods' => $routeMethods];
            } else {
                $routesData['acl'][] = ['name' => $routeName, 'path' => $routePath, 'permissionData' => $permission, 'methods' => $routeMethods];
            }
        }

        $comparator = function ($a, $b) {
            if ($a['path'] === $b['path']) {
                return 0;
            }

            return ($a['path'] < $b['path']) ? -1 : 1;
        };

        usort($routesData[self::MODE_DENY], $comparator);
        usort($routesData[self::MODE_ALLOW], $comparator);
        usort($routesData['acl'], $comparator);
        usort($routesData['free'], $comparator);

        return $routesData;
    }

    /**
     * @param bool $resource
     * @param null $resourceClass
     * @return false|string|null
     * @throws OmxUserResourceClassNotSetException
     */
    public function getUser(bool $resource = false, $resourceClass = null)
    {
        if (!$this->user) {
            return null;
        }

        if (!$resource) {
            return $this->user;
        }

        $resourceClass = $resourceClass ?: $this->userResourceClass;
        if (!$resourceClass) {
            throw new OmxUserResourceClassNotSetException;
        }

        return json_encode((new $resourceClass($this->user))->toResponse(app('request'))->getData()->data);
    }

    /**
     * @return bool
     */
    public function isDeepMode(): bool
    {
        return $this->deepMode;
    }

    /**
     * @return bool
     */
    public function isRoot(): bool
    {
        return in_array(ConstAcl::ROLE_ROOT, $this->roleList->toArray());
    }

    /**
     * @return bool
     */
    public function isUser(): bool
    {
        return in_array(ConstAcl::ROLE_USER, $this->roleList->toArray());
    }

    /**
     * @param $role
     * @param User|null $user
     */
    public function removeRole($role, User $user = null): void
    {
        ($user ?: $this->user)->roles()->detach($role);
    }

    /**
     * @param $permission
     * @param null $user
     */
    public function removePermission($permission, $user = null): void
    {
        ($user ?: $this->user)->permissions()->detach($permission);
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;

        $relations = array_merge($this->relationList, ['roles', 'roles.translates']);
        if ($this->isDeepMode()) {
            $relations[] = 'roles.permissions';
            $relations[] = 'roles.permissions.translates';
            $relations[] = 'permissions';
            $relations[] = 'permissions.translates';
        }
        $user->load($relations);

        $this->roleList = $user->roles;

        if ($this->isDeepMode()) {
            foreach ($user->roles as $role) {
                $this->permissionList = $this->permissionList->concat($role->permissions);
            }
            //Персонально назначенные пользователю привилегии могут иметь срок истечения
            $userPermissionList = $user->permissions->filter(function ($value, $key) {
                //TODO omadonex: корректность проверки даты, учитывая таймзоны
                $nowTs = Carbon::now()->timestamp;
                return is_null($value->expires_at) || (($value->expires_at > $nowTs) && ($value->starting_at < $nowTs));
            });
            $this->permissionList = $this->permissionList->concat($userPermissionList);
            $this->permissionList = $this->permissionList->unique->id->values();
        }

        if (!$this->roleList->count()) {
            $this->roleList->push(Role::with('translates')->find(ConstAcl::ROLE_USER));
        }
    }

    /**
     * @param string $routeName
     * @param array $routes
     * @param bool $assoc
     * @return array|string|bool
     */
    private function findWildcard(string $routeName, array $routes, bool $assoc = true)
    {
        $routeNameArr = explode('.', $routeName);
        $countParts = count($routeNameArr);
        if ($countParts > 1) {
            $key = null;
            $i = 0;
            $routeNamePath = $routeNameArr[$i];
            while ($i < $countParts - 1) {
                $routeKey = "{$routeNamePath}.*";
                if ($assoc) {
                    if (array_key_exists($routeKey, $routes)) {
                        $key = $routeKey;
                    }
                } else {
                    if (in_array($routeKey, $routes)) {
                        return true;
                    }
                }

                $routeNamePath .= '.' . $routeNameArr[++$i];
            }

            if ($assoc && $key) {
                return $routes[$key];
            }
        }

        return false;
    }

    private function runForUser($user, $func, $params)
    {
        $currUser = $this->user;
        $currPermissions = $this->permissionList;
        $currRoles = $this->roleList;

        $this->setUser($user);
        $result = call_user_func_array([$this, $func], $params);

        $this->user = $currUser;
        $this->permissions = $currPermissions;
        $this->roles = $currRoles;

        return $result;
    }

    /**
     * @param string $routeName
     * @param string $section
     * @return array|bool|string
     */
    protected function searchInSection(string $routeName, string $section)
    {
        //Ищем в группе роутов обычным способом
        $routes = $this->routeMap[$section];

        //Т.к. в группах deny и allow роуты указаны без ключей, просто перебором, то по группе определяем тип поиска
        if ($section === self::SECTION_PROTECTED) {
            if (array_key_exists($routeName, $routes)) {
                return $routes[$routeName];
            }
        } else {
            if (in_array($routeName, $routes)) {
                return true;
            }
        }

        //Ищем в группе роутов через wildcard
        return $this->findWildcard($routeName, $routes, $section === self::SECTION_PROTECTED);
    }
}