<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    $module = new otus_marketdemo();

    $module->UnInstallEvents();
    $module->UnInstallFiles();
    $module->UnInstallDB();

    ModuleManager::unRegisterModule($module->MODULE_ID);

    echo CAdminMessage::ShowNote(Loc::getMessage('OTUS_MARKETDEMO_UNINSTALL_SUCCESS'));

    return;
}

?>
<form action="<?= htmlspecialcharsbx($GLOBALS['APPLICATION']->GetCurPage()) ?>" method="post">
    <?= bitrix_sessid_post() ?>
    <p><?= Loc::getMessage('OTUS_MARKETDEMO_UNINSTALL_WARNING') ?></p>
    <p>
        <input type="checkbox" name="savedata" id="savedata" value="Y">
        <label for="savedata"><?= Loc::getMessage('OTUS_MARKETDEMO_UNINSTALL_SAVE_DATA') ?></label>
    </p>
    <input type="hidden" name="id" value="otus.marketdemo">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="submit" name="" value="<?= Loc::getMessage('OTUS_MARKETDEMO_UNINSTALL_SUBMIT') ?>">
</form>
