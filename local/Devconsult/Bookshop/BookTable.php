<?php

namespace Devconsult\Bookshop;

use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;

class BookTable extends DataManager
{
    private static bool $tableEnsured = false;
    private static bool $ensuringTable = false;

    /**
     * Ленивая миграция для учебного проекта:
     * - создаем таблицу, если ее еще нет;
     * - добавляем поле VERSION, если таблица уже существует, но колонка отсутствует.
     */
    protected static function ensureTableExists(): void
    {
        if (self::$tableEnsured || self::$ensuringTable) {
            return;
        }

        self::$ensuringTable = true;
        try {
            $connection = Application::getConnection();
            $tableName = static::getTableName();

            if (!$connection->isTableExists($tableName)) {
                Base::getInstance(static::class)->createDbTable();
            } else {
                $tableFields = array_change_key_case($connection->getTableFields($tableName), CASE_UPPER);
                if (!isset($tableFields['VERSION'])) {
                    $connection->queryExecute(
                        'ALTER TABLE ' . $tableName . ' ADD VERSION INT NOT NULL DEFAULT 1'
                    );
                }
            }

            self::$tableEnsured = true;
        } finally {
            self::$ensuringTable = false;
        }
    }

    public static function getTableName()
    {
        return 'devconsult_book_table';
    }

    public static function getMap()
    {
        static::ensureTableExists();

        return [
            // ID
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),

            // The name of the book
            new Entity\StringField('TITLE', [
                'required' => true,
            ]),

            // Author
            new Entity\StringField('AUTHOR', [
                'required' => true,
            ]),

            // Price
            new Entity\IntegerField('PRICE'),

            // Optimistic lock version
            new Entity\IntegerField('VERSION', [
                'required' => true,
                'default_value' => 1,
            ]),

            // Who created
            new Entity\IntegerField('CREATED_BY'),

            // Who updated
            new Entity\IntegerField('UPDATED_BY'),

            // Date of creation
            new Entity\DatetimeField('CREATED_AT', [
                'default_value' => function () {
                    return new DateTime();
                }
            ]),

            // Date of updating
            new Entity\DatetimeField('UPDATED_AT'),

            // ReferenceField to b_user table
            new Entity\ReferenceField(
                'CREATED_USER',
                UserTable::class,
                ['=this.CREATED_BY' => 'ref.ID']
            ),

            // ReferenceField to b_user table
            new Entity\ReferenceField(
                'UPDATED_USER',
                UserTable::class,
                ['=this.UPDATED_BY' => 'ref.ID']
            ),
        ];
    }
}
