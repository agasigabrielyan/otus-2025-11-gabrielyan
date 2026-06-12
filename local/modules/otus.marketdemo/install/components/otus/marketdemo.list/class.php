<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Otus\Marketdemo\ORM\DemoItemTable;

class MarketdemoListComponent extends CBitrixComponent
{
    public function executeComponent(): void
    {
        $this->arResult['ITEMS'] = [];

        if (!Loader::includeModule('otus.marketdemo')) {
            $this->includeComponentTemplate();
            return;
        }

        $filter = ['STATUS' => 'active'];
        if (($this->arParams['SHOW_EXPIRED'] ?? 'N') === 'Y') {
            $filter = [];
        }

        $result = DemoItemTable::getList([
            'select' => ['ID', 'TITLE', 'BODY', 'STATUS', 'DATE_PUBLISH'],
            'filter' => $filter,
            'order' => ['DATE_PUBLISH' => 'DESC'],
        ]);

        while ($row = $result->fetch()) {
            if ($row['DATE_PUBLISH'] instanceof \Bitrix\Main\Type\Date) {
                $row['DATE_PUBLISH_FORMATTED'] = $row['DATE_PUBLISH']->format('d.m.Y');
            } else {
                $row['DATE_PUBLISH_FORMATTED'] = (string)$row['DATE_PUBLISH'];
            }

            $this->arResult['ITEMS'][] = $row;
        }

        $this->includeComponentTemplate();
    }
}
