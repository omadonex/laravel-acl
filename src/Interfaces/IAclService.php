<?php

namespace Omadonex\LaravelAcl\Interfaces;

use App\Models\User;

interface IAclService
{
    const CHECK_TYPE_AND = 'and';
    const CHECK_TYPE_OR = 'or';

    const MODE_DENY = 'deny';
    const MODE_ALLOW = 'allow';

    const SECTION_ALLOWED = 'allowed';
    const SECTION_DENIED = 'denied';
    const SECTION_PROTECTED = 'protected';

    /**
     * @return int|null
     */
    public function id(): ?int;

    /**
     * @param $role
     * @param User|null $user
     */
    public function addRole($role, User $user = null): void;

    /**
     * @param $permission
     * @param User|null $user
     */
    public function addPermission($permission, User $user = null): void;

    /**
     * @param $permissions
     * @param string $type
     * @param bool $strict
     * @return bool
     */
    public function check($permissions, string $type = self::CHECK_TYPE_AND, bool $strict = false): bool;

    /**
     * @param $permissions
     * @param User $user
     * @param string $type
     * @param bool $strict
     * @return bool
     */
    public function checkForUser($permissions, User $user, string $type = self::CHECK_TYPE_AND, bool $strict = false): bool;

    /**
     * @param $roles
     * @param string $type
     * @param bool $strict
     * @return bool
     */
    public function checkRole($roles, string $type = self::CHECK_TYPE_AND, bool $strict = false): bool;

    /**
     * @param $roles
     * @param User $user
     * @param string $type
     * @param bool $strict
     * @return bool
     */
    public function checkRoleForUser($roles, User $user, string $type = self::CHECK_TYPE_AND, bool $strict = false): bool;

    /**
     * @param string $routeName
     * @param bool $strict
     * @return bool
     */
    public function checkRoute(string $routeName, bool $strict = false): bool;

    /**
     * @param string $routeName
     * @param User $user
     * @param bool $strict
     * @return bool
     */
    public function checkRouteForUser(string $routeName, User $user, bool $strict = false): bool;

    /**
     * @return array
     */
    public function getAllPermissionList(): array;

    /**
     * @param bool $permissions
     * @return array
     */
    public function getAllRoleList(bool $permissions = true): array;

    /**
     * @param bool $onlyNames
     * @return array
     */
    public function getPermissionList(bool $onlyNames = false): array;

    /**
     * @param bool $onlyNames
     * @return array
     */
    public function getRoleList(bool $onlyNames = false): array;

    /**
     * @return array
     */
    public function getRoutesData(): array;

    /**
     * @return bool
     */
    public function isDeepMode(): bool;

    /**
     * @return bool
     */
    public function isLoggedIn(): bool;

    /**
     * @return bool
     */
    public function isRoot(): bool;

    /**
     * @return bool
     */
    public function isUser(): bool;

    /**
     * @param bool $resource
     * @param null $resourceClass
     * @return mixed
     */
    public function refreshUser(bool $resource = false, $resourceClass = null);

    /**
     * @param $role
     * @param User|null $user
     */
    public function removeRole($role, User $user = null): void;

    /**
     * @param $permission
     * @param User|null $user
     */
    public function removePermission($permission, User $user = null): void;

    /**
     * @param User $user
     */
    public function setUser(User $user): void;

    /**
     * @return mixed
     */
    public function user(bool $resource = false, $resourceClass = null);
}