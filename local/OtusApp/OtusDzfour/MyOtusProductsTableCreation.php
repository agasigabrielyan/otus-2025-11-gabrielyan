<?php
namespace OtusApp\OtusDzfour;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;
use RuntimeException;

class MyOtusProductsTableCreation
{
    public static function migrateAndFillDemoData(): void
    {
        Loader::includeModule('iblock');

        // 1. Создаём инфоблоки (лениво)
        MyOtusCategoryIblockCreation::migrateAndFillDemoData();
        MyOtusManufacturerIblockCreation::migrateAndFillDemoData();

        // 2. Создаём таблицу products, если её нет
        self::ensureTable();

        // 3. Если товары уже есть — ничего не делаем
        if (ProductsTable::getCount() > 0) {
            return;
        }

        // 4. Получаем категории
        $categories = self::getElementsByIblockCode('category');
        $manufacturers = self::getElementsByIblockCode('manufacturer');

        if (empty($categories) || empty($manufacturers)) {
            throw new RuntimeException('Нет данных в CATEGORY или MANUFACTURER');
        }

        // 5. Тестовые продукты
        $products = [
            'УЗИ аппарат экспертного класса',
            'Кардиомонитор',
            'Рентген установка',
            'МРТ сканер',
            'Аппарат ЭКГ',
        ];

        foreach ($products as $name) {
            ProductsTable::add([
                'NAME'            => $name,
                'CATEGORY_ID'     => $categories[array_rand($categories)],
                'MANUFACTURER_ID' => $manufacturers[array_rand($manufacturers)],
                'PRICE'           => random_int(50_000, 2_000_000),
            ]);
        }
    }

    /**
     * Создание таблицы через ORM
     */
    private static function ensureTable(): void
    {
        $connection = Application::getConnection();

        if ($connection->isTableExists(ProductsTable::getTableName())) {
            return;
        }

        ProductsTable::getEntity()->createDbTable();
    }

    /**
     * Получение ID элементов инфоблока по CODE
     */
    private static function getElementsByIblockCode(string $code): array
    {
        $result = [];

        $res = ElementTable::getList([
            'select' => ['ID'],
            'filter' => [
                '=IBLOCK.CODE' => $code,
                '=ACTIVE'      => 'Y',
            ],
        ]);

        while ($row = $res->fetch()) {
            $result[] = (int)$row['ID'];
        }

        return $result;
    }
}