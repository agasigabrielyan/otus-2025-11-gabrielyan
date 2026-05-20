<?php
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
?>
<?= CAdminMessage::ShowNote(Loc::getMessage("MODULE_SUCCESFULLY_INSTALLED")); ?>
