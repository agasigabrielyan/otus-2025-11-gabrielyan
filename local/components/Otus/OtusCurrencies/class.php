<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
use Bitrix\Main\Loader;
use Bitrix\Currency\CurrencyTable;
use Bitrix\Currency\CurrencyRateTable;

class OtusCurrencies extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
    public function configureActions()
    {
        return [];
    }

    protected function getCurrentCurrencyData(): array
    {
        $result = [];

        if (!Loader::includeModule('currency')) {
            return $result;
        }

        $currencyCode = $this->arParams['CURRENCY'] ?? null;

        if (!$currencyCode) {
            return $result;
        }

        $currency = CurrencyTable::getList([
            'filter' => ['=CURRENCY' => $currencyCode],
            'select' => ['*']
        ])->fetch();

        if (!$currency) {
            return $result;
        }

        $lang = CCurrencyLang::GetByID($currencyCode, LANGUAGE_ID);
        $currency['FULL_NAME'] = $lang['FULL_NAME'] ?? $currencyCode;
        $currency['FORMAT'] = $lang['FORMAT_STRING'] ?? '';

        $rate = CurrencyRateTable::getList([
            'filter' => ['=CURRENCY' => $currencyCode],
            'order' => ['DATE_RATE' => 'DESC'],
            'limit' => 1,
            'select' => ['RATE', 'DATE_RATE']
        ])->fetch();

        $currency['RATE'] = $rate['RATE'] ?? 1;
        $currency['DATE_RATE'] = $rate['DATE_RATE'] ?? null;

        return $currency;
    }

    public function executeComponent()
    {
        $this->arResult['CURRENCY'] = $this->getCurrentCurrencyData();

        $this->includeComponentTemplate();
    }
}
