<?php

namespace Omadonex\LaravelAcl\Classes;

class ConstantsAcl
{
    const ROLE_ROOT = 'root';
    const ROLE_USER = 'user';

    const PRIVILEGE_GROUP_ID_DEFAULT = 'default';

    const ASSIGN_TYPE_SYSTEM = 1;
    const ASSIGN_TYPE_ROOT = 2;
    const ASSIGN_TYPE_USER = 3;
}