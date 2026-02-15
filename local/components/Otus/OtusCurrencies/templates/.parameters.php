<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Currency\CurrencyTable;

$currenciesList = CurrencyTable::getList()->fetchCollection();

$some = "";

$arComponentParameters = [
    'PARAMETERS' => [
        'CURRENCY' => [
            'NAME' => Loc::getMessage("CURRENCY"),
            'TYPE' => 'LIST',
            'MULTIPLE' => 'N',
            'VALUES' => []
        ]
    ]
];