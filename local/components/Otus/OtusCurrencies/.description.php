<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    'NAME' => Loc::getMessage("OTUS_CURRENCIES_COMPONENT_NAME"),
    'DESCRIPTION' => Loc::getMessage("OTUS_CURRENCIES_COMPONENT_DESCRIPTION"),
    'PATH' => [
        'ID' => 'OTUS_COMPONENTS',
        'NAME' => Loc::getMessage("OTUS_COMPANY_NAME"),
    ]
];