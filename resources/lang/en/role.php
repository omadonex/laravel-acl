<?php

use Omadonex\LaravelAcl\Classes\ConstAcl;

return [
    ConstAcl::ROLE_USER => [
        'name' => 'User',
        'description' => 'Default role for all users',
    ],

    ConstAcl::ROLE_ROOT => [
        'name' => 'Root',
        'description' => 'A role for super user (root)',
    ],
];