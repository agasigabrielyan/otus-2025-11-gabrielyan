<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

echo CAdminMessage::ShowNote(Loc::getMessage('OTUS_MARKETDEMO_MODULE_INSTALLED'));
