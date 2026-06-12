<?php

namespace Otus\Marketdemo\Agent;

use Bitrix\Main\Loader;
use Bitrix\Main\Type\Date;
use Otus\Marketdemo\ORM\DemoItemTable;

class ExpireAgent
{
    public static function run(): string
    {
        if (Loader::includeModule('otus.marketdemo')) {
            $today = new Date();

            $items = DemoItemTable::getList([
                'filter' => [
                    '<DATE_PUBLISH' => $today,
                    '=STATUS' => 'active',
                ],
                'select' => ['ID'],
            ]);

            while ($row = $items->fetch()) {
                DemoItemTable::update($row['ID'], ['STATUS' => 'expired']);
            }
        }

        return '\\' . __METHOD__ . '();';
    }
}
