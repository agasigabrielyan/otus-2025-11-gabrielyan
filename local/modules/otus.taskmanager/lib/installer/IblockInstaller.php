<?php
namespace Otus\TaskManager\Installer;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use CIBlock;
use CIBlockType;

Loc::loadMessages(__FILE__);

class IblockInstaller
{
    const MODULE_ID = 'otus.taskmanager';
    const IBLOCK_TYPE = 'otus_taskmanager';
    const IBLOCK_CODE = 'otus_tasks';
    const API_CODE = "otustasks";

    public static function install(): void
    {
        if (!Loader::includeModule('iblock')) {
            throw new \RuntimeException('Module iblock not installed');
        }

        self::createIblockType();
        self::createIblock();
    }

    public static function uninstall(bool $deleteData = false): void
    {
        if (!$deleteData || !Loader::includeModule('iblock')) {
            return;
        }

        self::deleteIblock();
        self::deleteIblockType();
    }

    private static function createIblockType(): void
    {
        if (CIBlockType::GetByID(self::IBLOCK_TYPE)->Fetch()) {
            return;
        }

        $iblockType = new CIBlockType();

        $fields = [
            'ID' => self::IBLOCK_TYPE,
            'SECTIONS' => 'Y',
            'SORT' => 100,
        ];

        if (!$iblockType->Add($fields)) {
            throw new \RuntimeException($iblockType->LAST_ERROR);
        }
    }

    private static function createIblock(): void
    {
        $existing = CIBlock::GetList([], [
            'CODE' => self::IBLOCK_CODE,
            'IBLOCK_TYPE_ID' => self::IBLOCK_TYPE
        ])->Fetch();

        if ($existing) {
            Option::set(self::MODULE_ID, 'IBLOCK_ID', $existing['ID']);
            return;
        }

        $sites = [];
        $rsSites = \Bitrix\Main\SiteTable::getList(['select' => ['LID']]);
        while ($site = $rsSites->fetch()) {
            $sites[] = $site['LID'];
        }

        $iblock = new CIBlock();
        $id = $iblock->Add([
            'ACTIVE' => 'Y',
            'NAME' => 'OTUS Task Manager: Tasks',
            'CODE' => self::IBLOCK_CODE,
            'IBLOCK_TYPE_ID' => self::IBLOCK_TYPE,
            'SITE_ID' => $sites,
            'SORT' => 100,
            'API_CODE' => self::API_CODE,
        ]);

        if (!$id) {
            throw new \RuntimeException($iblock->LAST_ERROR);
        }

        Option::set(self::MODULE_ID, 'IBLOCK_ID', $id);
    }

    private static function deleteIblock(): void
    {
        $id = Option::get(self::MODULE_ID, 'IBLOCK_ID');
        if ($id) {
            CIBlock::Delete((int)$id);
            Option::delete(self::MODULE_ID);
        }
    }

    private static function deleteIblockType(): void
    {
        $count = CIBlock::GetList([], ['IBLOCK_TYPE_ID' => self::IBLOCK_TYPE])->SelectedRowsCount();
        if ($count === 0) {
            CIBlockType::Delete(self::IBLOCK_TYPE);
        }
    }
}