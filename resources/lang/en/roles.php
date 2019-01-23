<?php

use Omadonex\LaravelAcl\Classes\ConstantsAcl;

return [
    ConstantsAcl::ROLE_USER => [
        'name' => 'User',
        'description' => 'Default role for all users',
    ],

    ConstantsAcl::ROLE_ROOT => [
        'name' => 'Root',
        'description' => 'A role for super privileged user (root)',
    ],
];