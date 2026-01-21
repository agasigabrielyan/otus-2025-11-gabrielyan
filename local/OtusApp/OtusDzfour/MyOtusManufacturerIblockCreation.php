<?php
namespace OtusApp\OtusDzfour;

use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader;
use CIBlock;
use CIBlockElement;
use CIBlockProperty;

class MyOtusManufacturerIblockCreation
{
    public static function migrateAndFillDemoData(): int
    {
        Loader::includeModule('iblock');

        $iblockId = self::getOrCreateIblock('Производители', 'manufacturer');

        // Свойство "Страна"
        self::createPropertyIfNotExists([
            'IBLOCK_ID'     => $iblockId,
            'NAME'          => 'Страна',
            'CODE'          => 'COUNTRY',
            'PROPERTY_TYPE' => 'S',
            'ACTIVE'        => 'Y',
        ]);

        $manufacturers = [
            ['NAME' => 'Siemens Healthineers', 'COUNTRY' => 'Германия'],
            ['NAME' => 'GE Healthcare',        'COUNTRY' => 'США'],
            ['NAME' => 'Philips Healthcare',   'COUNTRY' => 'Нидерланды'],
            ['NAME' => 'Canon Medical',        'COUNTRY' => 'Япония'],
            ['NAME' => 'Mindray',              'COUNTRY' => 'Китай'],
        ];

        $el = new CIBlockElement();

        foreach ($manufacturers as $item) {
            $exists = CIBlockElement::GetList(
                [],
                ['IBLOCK_ID' => $iblockId, 'NAME' => $item['NAME']],
                false,
                ['nTopCount' => 1]
            )->Fetch();

            if ($exists) {
                continue;
            }

            $id = $el->Add([
                'IBLOCK_ID'       => $iblockId,
                'NAME'            => $item['NAME'],
                'ACTIVE'          => 'Y',
                'PROPERTY_VALUES' => [
                    'COUNTRY' => $item['COUNTRY'],
                ],
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

    private static function createPropertyIfNotExists(array $fields): void
    {
        $exists = PropertyTable::getList([
            'filter' => [
                '=CODE'      => $fields['CODE'],
                '=IBLOCK_ID' => $fields['IBLOCK_ID'],
            ],
        ])->fetch();

        if ($exists) {
            return;
        }

        $prop = new CIBlockProperty();
        $prop->Add($fields);
    }
}