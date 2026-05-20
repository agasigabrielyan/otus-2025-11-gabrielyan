<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;
use Otus\TaskManager\Installer\IblockInstaller;
use Otus\TaskManager\Installer\TableInstaller;
use Otus\TaskManager\Updater\IblockUpdater;
use Otus\TaskManager\Updater\TableUpdater;

Loc::loadMessages(__FILE__);

class otus_taskmanager extends CModule
{
    public $MODULE_ID;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;
    public $MODULE_SORT;
    public $SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
    public $MODULE_GROUP_RIGHTS = 'Y';

    public function __construct()
    {
        $this->MODULE_ID = 'otus.taskmanager';
        $arModuleVersion = [];
        include __DIR__ . '/version.php';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('OTUS_TASKMANAGER_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage("OTUS_TASKMANAGER_MODULE_DESC");
        $this->PARTNER_NAME = Loc::getMessage('PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('PARTNER_URI');
    }

    public function DoInstall()
    {
        global $APPLICATION;

        ModuleManager::registerModule($this->MODULE_ID);

        if(!Loader::includeModule($this->MODULE_ID)) {
            throw new \Exception(Loc::getMessage('MODULE_LOAD_FAILED'));
        }

        IblockInstaller::install();
        TableInstaller::install();

        CopyDirFiles(
            __DIR__ . "/components/otus",
            $_SERVER["DOCUMENT_ROOT"] . "/local/components/otus",
            true,
            true
        );

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("OTUS_TASKMANAGER_INSTALL_TITLE"),
            __DIR__ . "/step.php"
        );


        return true;
    }

    public function DoUninstall()
    {
        global $APPLICATION;

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("OTUS_TASKMANAGER_UNINSTALL_TITLE"),
            __DIR__ . "/unstep.php"
        );
    }

}