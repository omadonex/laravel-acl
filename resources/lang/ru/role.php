<?php

use Omadonex\LaravelAcl\Classes\ConstAcl;

return [
    ConstAcl::ROLE_USER => [
        'name' => 'Пользователь',
        'description' => 'Роль по умолчанию для всех пользователей',
    ],

    ConstAcl::ROLE_ROOT => [
        'name' => 'Root',
        'description' => 'Роль для пользователя с самыми широкими правами (root)',
    ],
];