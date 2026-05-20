<?php

namespace Agasicompany\Periodlock;

use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;

/**
 * Универсальная таблица блокировки периодов для любых сущностей.
 *
 * ENTITY_CODE — код сущности/раздела (например: revenue, direct_costs, products).
 * DATE_FROM, DATE_TO — период запрета/блокировки редактирования.
 * IS_LOCKED — Y: период закрыт, N: открыт.
 */
class PeriodLockTable extends DataManager
{
    private static bool $tableEnsured = false;
    private static bool $ensuringTable = false;

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

                if (!isset($tableFields['DATE_FROM'])) {
                    $connection->queryExecute(
                        'ALTER TABLE ' . $tableName . ' ADD DATE_FROM DATE NULL, ADD DATE_TO DATE NULL'
                    );

                    if (isset($tableFields['YEAR'])) {
                        $connection->queryExecute(
                            'UPDATE ' . $tableName
                            . " SET DATE_FROM = CONCAT(YEAR, '-01-01'), DATE_TO = CONCAT(YEAR, '-12-31')"
                            . ' WHERE DATE_FROM IS NULL'
                        );
                    }

                    $connection->queryExecute(
                        'ALTER TABLE ' . $tableName
                        . ' MODIFY DATE_FROM DATE NOT NULL, MODIFY DATE_TO DATE NOT NULL'
                    );
                }
            }

            PeriodLockRoleTable::ensureTableExists();

            self::$tableEnsured = true;
        } finally {
            self::$ensuringTable = false;
        }
    }

    public static function getTableName(): string
    {
        return 'green_entity_period_lock';
    }

    public static function getMap(): array
    {
        static::ensureTableExists();

        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),

            new Entity\StringField('ENTITY_CODE', [
                'required' => true,
            ]),

            new DateField('DATE_FROM', [
                'required' => true,
            ]),

            new DateField('DATE_TO', [
                'required' => true,
            ]),

            new Entity\BooleanField('IS_LOCKED', [
                'values' => ['N', 'Y'],
                'default_value' => 'N',
            ]),

            new Entity\IntegerField('LOCKED_BY', [
                'nullable' => true,
            ]),

            new Entity\DatetimeField('LOCKED_AT', [
                'nullable' => true,
            ]),

            new Entity\IntegerField('UNLOCKED_BY', [
                'nullable' => true,
            ]),

            new Entity\DatetimeField('UNLOCKED_AT', [
                'nullable' => true,
            ]),

            new Entity\DatetimeField('CREATED_AT', [
                'default_value' => static fn() => new DateTime(),
            ]),

            new Entity\DatetimeField('UPDATED_AT', [
                'default_value' => static fn() => new DateTime(),
            ]),

            new Entity\ReferenceField(
                'LOCKED_USER',
                UserTable::class,
                ['=this.LOCKED_BY' => 'ref.ID']
            ),

            new Entity\ReferenceField(
                'UNLOCKED_USER',
                UserTable::class,
                ['=this.UNLOCKED_BY' => 'ref.ID']
            ),

            new OneToMany(
                'ROLES',
                PeriodLockRoleTable::class,
                'PERIOD_LOCK'
            ),
        ];
    }
}
