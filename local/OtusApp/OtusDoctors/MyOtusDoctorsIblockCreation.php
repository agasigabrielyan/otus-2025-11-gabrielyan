<?php
namespace OtusApp\OtusDoctors;

use Bitrix\Main\Loader;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;
use CIBlock;
use CIBlockElement;
use CIBlockProperty;
use CIBlockPropertyEnum;

class MyOtusDoctorsIblockCreation
{
    public static function migrateAndFillDemoData(): int
    {
        Loader::includeModule("iblock");

        // --- Создание инфоблоков ---
        $proceduresIblockId = self::getOrCreateIblock('Процедуры', 'procedures');
        $doctorsIblockId = self::getOrCreateIblock('Доктора', 'doctors');

        // --- Создание свойств ---
        self::createPropertyIfNotExists([
            'IBLOCK_ID' => $doctorsIblockId,
            'NAME' => 'Процедуры',
            'CODE' => 'PROCEDURES',
            'PROPERTY_TYPE' => 'E',
            'LINK_IBLOCK_ID' => $proceduresIblockId,
            'MULTIPLE' => 'Y',
            'ACTIVE' => 'Y'
        ]);

        self::createPropertyIfNotExists([
            'IBLOCK_ID' => $doctorsIblockId,
            'NAME' => 'Кабинет',
            'CODE' => 'CABINET',
            'PROPERTY_TYPE' => 'L',
            'LIST_TYPE' => 'L',
            'ACTIVE' => 'Y',
            'VALUES' => array_map(fn($i) => ['VALUE' => "№$i"], range(1, 9))
        ]);

        // --- Заполняем процедуры ---
        $procedures = [
            'УЗИ','ЭКГ','МРТ','КТ','Рентгенография',
            'Эндоскопия','Колоноскопия','Гастроскопия',
            'Лабораторные анализы крови','Функциональная диагностика'
        ];
        $proceduresIds = [];
        $el = new CIBlockElement();
        foreach ($procedures as $name) {
            $exists = CIBlockElement::GetList([], ['IBLOCK_ID'=>$proceduresIblockId,'NAME'=>$name], false, ['nTopCount'=>1])->Fetch();
            if ($exists) {
                $proceduresIds[] = $exists['ID'];
                continue;
            }
            $id = $el->Add(['IBLOCK_ID'=>$proceduresIblockId,'NAME'=>$name,'ACTIVE'=>'Y']);
            if (!$id) throw new \RuntimeException($el->LAST_ERROR);
            $proceduresIds[] = $id;
        }

        // --- Получаем ID значений кабинета ---
        $cabinetProp = CIBlockProperty::GetList([], ['IBLOCK_ID'=>$doctorsIblockId,'CODE'=>'CABINET'])->Fetch();
        $cabinetValues = [];
        $enum = CIBlockPropertyEnum::GetList([], ['PROPERTY_ID'=>$cabinetProp['ID']]);
        while ($val = $enum->Fetch()) $cabinetValues[] = $val['ID'];

        // --- Демо доктора с рандомными кабинетами ---
        $demoDoctors = [
            'Иванов Иван Иванович' => [$proceduresIds[0], $proceduresIds[1]],
            'Петров Петр Петрович' => [$proceduresIds[2]],
            'Сидорова Анна Сергеевна' => [$proceduresIds[0]],
            'Кузнецов Дмитрий Олегович' => [$proceduresIds[3], $proceduresIds[4]],
            'Морозова Елена Викторовна' => [$proceduresIds[8]],
            'Алексеев Михаил Андреевич' => [$proceduresIds[5], $proceduresIds[6]],
            'Романова Ольга Николаевна' => [$proceduresIds[7]],
            'Захаров Артем Валерьевич' => [$proceduresIds[1], $proceduresIds[9]],
            'Белова Наталья Игоревна' => [$proceduresIds[0], $proceduresIds[9]],
        ];

        foreach ($demoDoctors as $name => $procIds) {
            $exists = CIBlockElement::GetList([], ['IBLOCK_ID'=>$doctorsIblockId,'NAME'=>$name], false, ['nTopCount'=>1])->Fetch();
            if ($exists) continue;

            $randomCabinetId = $cabinetValues[array_rand($cabinetValues)];

            $res = $el->Add([
                'IBLOCK_ID'=>$doctorsIblockId,
                'NAME'=>$name,
                'ACTIVE'=>'Y',
                'PROPERTY_VALUES'=>[
                    'CABINET' => $randomCabinetId,
                    'PROCEDURES' => $procIds
                ]
            ]);

            if (!$res) throw new \RuntimeException($el->LAST_ERROR);
        }

        return (int)$doctorsIblockId;
    }

    private static function getOrCreateIblock(string $name, string $code): int
    {
        $iblock = IblockTable::getList([
            'filter' => ['=CODE' => $code],
            'select' => ['ID']
        ])->fetch();

        if ($iblock) return (int)$iblock['ID'];

        $ib = new CIBlock();
        $id = $ib->Add([
            'NAME'=>$name,
            'CODE'=>$code,
            'API_CODE' => $code,
            'IBLOCK_TYPE_ID'=>'lists',
            'SITE_ID'=>['s1'],
            'ACTIVE'=>'Y',
            'VERSION'=>2,

        ]);
        if (!$id) throw new \RuntimeException($ib->LAST_ERROR);
        return (int)$id;
    }

    private static function createPropertyIfNotExists(array $fields): void
    {
        $exists = PropertyTable::getList([
            'filter'=>[
                '=CODE'=>$fields['CODE'],
                '=IBLOCK_ID'=>$fields['IBLOCK_ID']
            ]
        ])->fetch();

        if ($exists) return;

        $prop = new CIBlockProperty();
        $prop->Add($fields);
    }
}