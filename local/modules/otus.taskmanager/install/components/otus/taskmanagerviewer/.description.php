<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentDescription = [
    'NAME' => Loc::getMessage('OTUS_TASKMANAGER__NAME'),
    'DESCRIPTION' => Loc::getMessage('OTUS_TASKMANAGER__DESCRIPTION'),
    'PATH' => [
        'ID' => 'OTUS_COMPANY',
        'NAME' => Loc::getMessage('OTUS_COMPANY_NAME'),
    ]
];