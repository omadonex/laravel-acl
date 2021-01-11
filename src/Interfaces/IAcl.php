<?php

namespace Omadonex\LaravelAcl\Interfaces;

interface IAcl
{
    public function isDeepMode();

    public function getAvailableRoles($permissions = true);

    public function getAvailablePermissions();

    public function loggedIn();

    public function setUser($user);

    public function getUser();

    public function getRoles($onlyNames = false);

    public function getPermissions($onlyNames = false);

    public function hasRoles($roles);

    public function checkRoles($rolesCombined, $rootStrict = false);

    public function check($permissions);

    public function addRole($role, $user = null);

    public function addPermission($permission, $user = null);

    public function removeRole($role, $user = null);

    public function removePermission($permission, $user = null);
}