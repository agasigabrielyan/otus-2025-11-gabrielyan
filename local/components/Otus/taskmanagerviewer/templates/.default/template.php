<?php
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Localization\Loc;
Asset::getInstance()->addJs('/local/js/task-manager-form.js'); // Подключаем JS
?>
<form id="task-form">
    <label>
        <?= Loc::getMessage('TASK_NAME') ?>
        <input type="text" name="NAME" required />
    </label>
    <br/>
    <label>
        <?= Loc::getMessage('TASK_DESCRIPTION') ?>
        <textarea name="DESCRIPTION"></textarea>
    </label>
    <br/>
    <label>
        <?= Loc::getMessage('TASK_USERS') ?>
        <input type="text" name="USERS" placeholder="ID через запятую" />
    </label>
    <br/>
    <button type="submit"><?= Loc::getMessage('CREATE_TASK') ?></button>
</form>

<div id="task-result"></div>