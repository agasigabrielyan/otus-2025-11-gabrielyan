<?php
namespace OtusApp\OtusDzfour;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Loader;
use CIBlock;
use CIBlockElement;

class MyOtusCategoryIblockCreation
{
    public static function migrateAndFillDemoData(): int
    {
        Loader::includeModule('iblock');

        $iblockId = self::getOrCreateIblock('Категории', 'category');

        $categories = [
            'Диагностика',
            'Хирургия',
            'Терапия',
            'Кардиология',
            'Неврология',
        ];

        $el = new CIBlockElement();

        foreach ($categories as $name) {
            $exists = CIBlockElement::GetList(
                [],
                ['IBLOCK_ID' => $iblockId, 'NAME' => $name],
                false,
                ['nTopCount' => 1]
            )->Fetch();

            if ($exists) {
                continue;
            }

            $id = $el->Add([
                'IBLOCK_ID' => $iblockId,
                'NAME'      => $name,
                'ACTIVE'    => 'Y',
            ]);

            if (!$id) {
                throw new \RuntimeException($el->LAST_ERROR);
            }
        }

        return (int)$iblockId;
    }

    private static function getOrCreateIblock(string $name, string $code): int
    {
        $iblock = IblockTable::getList([
            'filter' => ['=CODE' => $code],
            'select' => ['ID'],
        ])->fetch();

        if ($iblock) {
            return (int)$iblock['ID'];
        }

        $ib = new CIBlock();
        $id = $ib->Add([
            'NAME'          => $name,
            'CODE'          => $code,
            'API_CODE'      => $code,
            'IBLOCK_TYPE_ID'=> 'lists',
            'SITE_ID'       => ['s1'],
            'ACTIVE'        => 'Y',
            'VERSION'       => 2,
        ]);

        if (!$id) {
            throw new \RuntimeException($ib->LAST_ERROR);
        }

        return (int)$id;
    }
}