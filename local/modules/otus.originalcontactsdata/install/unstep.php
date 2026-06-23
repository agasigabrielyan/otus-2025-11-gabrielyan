<?php

if (!check_bitrix_sessid()) {
    return;
}
?>
<form action="<?= htmlspecialcharsbx($APPLICATION->GetCurPage()) ?>">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <p>Module otus.originalcontactsdata has been uninstalled.</p>
    <input type="submit" name="" value="<?= GetMessage('MOD_BACK') ?>">
</form>
