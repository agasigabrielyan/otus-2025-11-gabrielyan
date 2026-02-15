<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Currency\CurrencyTable;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('currency')) {
    return;
}

$allTheCurrencies = [];

// выбраем все валюты из таблицы
$currencies = CurrencyTable::getList([
    'select' => ['CURRENCY'],
    'order' => ['CURRENCY' => 'ASC'],
])->fetchAll();

foreach ($currencies as $currency) {
    $code = $currency['CURRENCY'];

    $lang = CCurrencyLang::GetByID($code, LANGUAGE_ID);
    $name = $lang['FULL_NAME'] ?? $code;

    $allTheCurrencies[$code] = $code . ' — ' . $name;
}

// Параметры компонента
$arComponentParameters = [
    'PARAMETERS' => [
        'CURRENCY' => [
            'NAME' => Loc::getMessage('CURRENCY') ?: 'Валюта',
            'TYPE' => 'LIST',
            'MULTIPLE' => 'N',
            'VALUES' => $allTheCurrencies,
            'DEFAULT' => 'USD',
        ],
    ],
];