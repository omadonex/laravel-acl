<?php

namespace Omadonex\LaravelAcl\Interfaces;

interface IAcl
{
    public function isDeepMode();

    public function getAvailableRoles($privileges = true);

    public function getAvailablePrivileges();

    public function loggedIn();

    public function setUser($user);

    public function getUser();

    public function getRoles($onlyNames = false);

    public function getPrivileges($onlyNames = false);

    public function hasRoles($roles);

    public function checkRoles($rolesCombined, $rootStrict = false);

    public function check($privileges);

    public function addRole($role, $user = null);

    public function addPrivilege($privilege, $user = null);

    public function removeRole($role, $user = null);

    public function removePrivilege($privilege, $user = null);
}