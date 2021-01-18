<?php

use Omadonex\LaravelAcl\Interfaces\IAclService;

return [
    //Доступ закрыт для всех, кроме ROOT
    IAclService::SECTION_DENIED => [
    ],

    //Доступ открыт для всех
    IAclService::SECTION_ALLOWED => [
    ],

    //Требуется разрешение для доступа (для ROOT все открыто)
    IAclService::SECTION_PROTECTED => [
    ],
];
