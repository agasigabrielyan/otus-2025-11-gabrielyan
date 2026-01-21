<?php
namespace OtusApp\OtusDzfour;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Query\Join;

class ProductsTable extends DataManager
{
    /** @var bool */
    private static bool $iblocksEnsured = false;

    public static function getTableName()
    {
        return 'otus_products';
    }

    /**
     * Ленивая проверка и создание инфоблоков
     * ВАЖНО: вызывается ДО любых ORM-запросов
     */
    public static function ensureIblocks(): void
    {
        if (self::$iblocksEnsured) {
            return;
        }

        Loader::includeModule('iblock');

        MyOtusCategoryIblockCreation::migrateAndFillDemoData();
        MyOtusManufacturerIblockCreation::migrateAndFillDemoData();

        self::$iblocksEnsured = true;
    }

    public static function getMap()
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary(true)
                ->configureAutocomplete(true),

            (new StringField('NAME'))
                ->configureRequired(true),

            (new IntegerField('CATEGORY_ID'))
                ->configureRequired(true),

            (new IntegerField('MANUFACTURER_ID'))
                ->configureRequired(true),

            (new IntegerField('PRICE'))
                ->configureRequired(true),

            // CATEGORY (1 Category → N Products)
            (new Reference(
                'CATEGORY',
                ElementTable::class,
                Join::on('this.CATEGORY_ID', 'ref.ID')
            ))->configureJoinType('inner'),

            // MANUFACTURER (1 Manufacturer → N Products)
            (new Reference(
                'MANUFACTURER',
                ElementTable::class,
                Join::on('this.MANUFACTURER_ID', 'ref.ID')
            ))->configureJoinType('inner'),
        ];
    }
}