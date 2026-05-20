<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Otus\TaskManager\Installer\IblockInstaller;
use Bitrix\Main\ModuleManager;
use Otus\TaskManager\Installer\TableInstaller;

Loc::loadMessages(__FILE__);

if( $_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid() ) {
    $deletedData = ($_POST['delete_data'] === 'Y');

    if(!Loader::includeModule("otus.taskmanager")) {
        throw new Exception(Loc::getMessage("OTUS_TASKMANAGER_MODULE_IS_NOT_INSTALLED"));
    }

    IblockInstaller::uninstall($deletedData);
    TableInstaller::uninstall();

    DeleteDirFilesEx("/local/components/otus/taskmanagerviewer");

    ModuleManager::unRegisterModule("otus.taskmanager");

    echo CAdminMessage::ShowNote(Loc::getMessage("OTUS_TASKMANAGER_UNINSTALL_SUCCESS"));

    return;
}
?>
<form method="post">
    <?= bitrix_sessid_post(); ?>
    <label>
        <?= Loc::getMessage("DELETE_IBLOCK_OR_NOT"); ?>
        <input type="checkbox" name="delete_data" value="Y" />
    </label>
    <br/>
    <input type="submit" value="<?= Loc::getMessage("NEXT_STEP_OF_DELETION")?>" />
</form>
