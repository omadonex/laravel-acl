<?php

use Omadonex\LaravelAcl\Classes\ConstantsAcl;

return [
    ConstantsAcl::ROLE_USER => [
        'name' => 'Пользователь',
        'description' => 'Роль по умолчанию для всех пользователей',
    ],

    ConstantsAcl::ROLE_ROOT => [
        'name' => 'Root',
        'description' => 'Роль для пользователя с самыми широкими правами (root)',
    ],
];