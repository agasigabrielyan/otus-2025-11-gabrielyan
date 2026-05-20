<?php

namespace Agasicompany\Periodlock;

use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Entity\DataManager;
/**
 * Связь period lock ↔ роли (значения списка UF_KPI_EXTRA_ROLES).
 * Одна запись period lock — много строк в этой таблице (1:N).
 */
class PeriodLockRoleTable extends DataManager
{
    private static bool $tableEnsured = false;
    private static bool $ensuringTable = false;

    public static function ensureTableExists(): void
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
            }

            self::$tableEnsured = true;
        } finally {
            self::$ensuringTable = false;
        }
    }

    public static function getTableName(): string
    {
        return 'green_entity_period_lock_role';
    }

    public static function getMap(): array
    {
        static::ensureTableExists();

        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),

            new Entity\IntegerField('PERIOD_LOCK_ID', [
                'required' => true,
            ]),

            new Entity\IntegerField('ROLE_ENUM_ID', [
                'required' => true,
            ]),

            new Entity\ReferenceField(
                'PERIOD_LOCK',
                PeriodLockTable::class,
                ['=this.PERIOD_LOCK_ID' => 'ref.ID']
            ),
        ];
    }
}
