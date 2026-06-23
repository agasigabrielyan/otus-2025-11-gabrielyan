<?php

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Otus\OriginalContactsData\OriginalContactsDataTable;
use Otus\OriginalContactsData\Rest\OriginalContactsDataRest;

Loc::loadMessages(__FILE__);

class otus_originalcontactsdata extends CModule
{
    public $MODULE_ID = 'otus.originalcontactsdata';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME = 'OTUS';
    public $PARTNER_URI = 'https://otus.ru';

    private const REST_SCOPE_CACHE_DIR = '/rest/scope/';

    public function __construct()
    {
        $arModuleVersion = [];
        include __DIR__ . '/version.php';

        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('OTUS_ORIGINALCONTACTSDATA_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('OTUS_ORIGINALCONTACTSDATA_MODULE_DESCRIPTION');
    }

    public function DoInstall(): bool
    {
        global $APPLICATION;

        if (!$this->installDependencies()) {
            return false;
        }

        ModuleManager::registerModule($this->MODULE_ID);

        if (!$this->InstallDB()) {
            ModuleManager::unRegisterModule($this->MODULE_ID);
            return false;
        }

        if (!$this->InstallEvents()) {
            ModuleManager::unRegisterModule($this->MODULE_ID);
            return false;
        }

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage('OTUS_ORIGINALCONTACTSDATA_INSTALL_TITLE'),
            __DIR__ . '/step.php'
        );

        return true;
    }

    public function DoUninstall(): void
    {
        global $APPLICATION;

        $context = Application::getInstance()->getContext()->getRequest();
        $saveData = $context->get('savedata') === 'Y';

        $this->UnInstallEvents();
        $this->UnInstallDB(['savedata' => $saveData ? 'Y' : 'N']);
        ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage('OTUS_ORIGINALCONTACTSDATA_UNINSTALL_TITLE'),
            __DIR__ . '/unstep.php'
        );
    }

    public function InstallDB(): bool
    {
        if (!Loader::includeModule($this->MODULE_ID)) {
            return false;
        }

        $connection = Application::getConnection();
        $tableName = OriginalContactsDataTable::getTableName();

        if (!$connection->isTableExists($tableName)) {
            OriginalContactsDataTable::getEntity()->createDbTable();
        }

        return true;
    }

    public function UnInstallDB(array $params = []): bool
    {
        if (($params['savedata'] ?? 'N') === 'Y') {
            return true;
        }

        if (!Loader::includeModule($this->MODULE_ID)) {
            Loader::registerAutoLoadClasses($this->MODULE_ID, [
                'Otus\\OriginalContactsData\\OriginalContactsDataTable' => 'lib/originalcontactsdatatable.php',
            ]);
        }

        $connection = Application::getConnection();
        $tableName = OriginalContactsDataTable::getTableName();

        if ($connection->isTableExists($tableName)) {
            $connection->dropTable($tableName);
        }

        return true;
    }

    public function InstallEvents(): bool
    {
        if (!Loader::includeModule('rest')) {
            return false;
        }

        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandlerCompatible(
            'rest',
            'OnRestServiceBuildDescription',
            $this->MODULE_ID,
            OriginalContactsDataRest::class,
            'onRestServiceBuildDescription'
        );

        \Bitrix\Rest\Event\Sender::bind('main', OriginalContactsDataRest::EVENT_INTERNAL_NAME);

        $this->clearRestScopeCache();

        return true;
    }

    public function UnInstallEvents(): void
    {
        if (!Loader::includeModule('rest')) {
            return;
        }

        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'rest',
            'OnRestServiceBuildDescription',
            $this->MODULE_ID,
            OriginalContactsDataRest::class,
            'onRestServiceBuildDescription'
        );

        \Bitrix\Rest\Event\Sender::unbind('main', OriginalContactsDataRest::EVENT_INTERNAL_NAME);

        $this->clearRestScopeCache();
    }

    private function clearRestScopeCache(): void
    {
        Cache::clearCache(true, self::REST_SCOPE_CACHE_DIR);
    }

    private function installDependencies(): bool
    {
        return Loader::includeModule('main') && Loader::includeModule('rest');
    }
}
