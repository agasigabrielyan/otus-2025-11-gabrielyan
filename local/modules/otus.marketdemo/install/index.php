<?php

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Otus\Marketdemo\Event\UserHandler;
use Otus\Marketdemo\ORM\DemoItemTable;

Loc::loadMessages(__FILE__);

class otus_marketdemo extends CModule
{
    public $MODULE_ID;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;
    public $MODULE_GROUP_RIGHTS = 'Y';

    public function __construct()
    {
        $this->MODULE_ID = 'otus.marketdemo';

        $arModuleVersion = [];
        include __DIR__ . '/version.php';

        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('OTUS_MARKETDEMO_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('OTUS_MARKETDEMO_MODULE_DESC');
        $this->PARTNER_NAME = Loc::getMessage('PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('PARTNER_URI');
    }

    public function DoInstall(): bool
    {
        global $APPLICATION;

        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallFiles();

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage('OTUS_MARKETDEMO_INSTALL_TITLE'),
            __DIR__ . '/step.php'
        );

        return true;
    }

    public function DoUninstall(): void
    {
        global $APPLICATION;

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage('OTUS_MARKETDEMO_UNINSTALL_TITLE'),
            __DIR__ . '/unstep.php'
        );
    }

    public function InstallDB(): bool
    {
        if (!ModuleManager::isModuleInstalled($this->MODULE_ID)) {
            ModuleManager::registerModule($this->MODULE_ID);
        }

        if (!Loader::includeModule($this->MODULE_ID)) {
            return false;
        }

        $connection = Application::getConnection();
        $tableName = DemoItemTable::getEntity()->getDBTableName();

        if (!$connection->isTableExists($tableName)) {
            DemoItemTable::getEntity()->createDbTable();
        }

        $this->installAgents();

        return true;
    }

    public function UnInstallDB(): bool
    {
        CAgent::RemoveModuleAgents($this->MODULE_ID);

        if (($_REQUEST['savedata'] ?? '') === 'Y') {
            return true;
        }

        if (!Loader::includeModule($this->MODULE_ID)) {
            return false;
        }

        $connection = Application::getConnection();
        $tableName = DemoItemTable::getEntity()->getDBTableName();

        if ($connection->isTableExists($tableName)) {
            $connection->dropTable($tableName);
        }

        return true;
    }

    public function InstallEvents(): bool
    {
        EventManager::getInstance()->registerEventHandler(
            'main',
            'OnAfterUserUpdate',
            $this->MODULE_ID,
            UserHandler::class,
            'onAfterUserUpdate'
        );

        return true;
    }

    public function UnInstallEvents(): bool
    {
        EventManager::getInstance()->unRegisterEventHandler(
            'main',
            'OnAfterUserUpdate',
            $this->MODULE_ID,
            UserHandler::class,
            'onAfterUserUpdate'
        );

        return true;
    }

    public function InstallFiles(): bool
    {
        $moduleRoot = dirname(__DIR__);

        $adminListFile = $moduleRoot . '/admin/otus_marketdemo_list.php';
        if (is_file($adminListFile)) {
            CopyDirFiles(
                $adminListFile,
                $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/',
                true,
                true
            );
        }

        $componentsFrom = $moduleRoot . '/install/components/otus';
        $componentsTo = $_SERVER['DOCUMENT_ROOT'] . '/local/components/otus';
        if (is_dir($componentsFrom)) {
            CopyDirFiles($componentsFrom, $componentsTo, true, true);
        }

        return true;
    }

    public function UnInstallFiles(): bool
    {
        $adminListFile = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/otus_marketdemo_list.php';
        if (is_file($adminListFile)) {
            @unlink($adminListFile);
        }

        DeleteDirFilesEx('/local/components/otus/marketdemo.list');

        return true;
    }

    private function installAgents(): void
    {
        $agentName = '\\Otus\\Marketdemo\\Agent\\ExpireAgent::run();';
        $interval = max(1, (int)Option::get($this->MODULE_ID, 'agent_interval', 60));

        $exists = CAgent::GetList([], [
            'NAME' => $agentName,
            'MODULE_ID' => $this->MODULE_ID,
        ])->Fetch();

        if (!$exists) {
            CAgent::AddAgent(
                $agentName,
                $this->MODULE_ID,
                'N',
                $interval,
                '',
                'Y',
                '',
                100
            );
        }
    }
}
