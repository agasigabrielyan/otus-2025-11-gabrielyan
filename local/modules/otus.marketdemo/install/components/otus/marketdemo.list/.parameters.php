<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'PARAMETERS' => [
        'SHOW_EXPIRED' => [
            'NAME' => 'Показывать просроченные записи',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'N',
        ],
    ],
];
