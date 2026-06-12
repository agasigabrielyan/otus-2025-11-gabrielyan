<?php

use Bitrix\Main\Loader;
use Otus\Marketdemo\ORM\DemoItemTable;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

$moduleId = 'otus.marketdemo';

if (!Loader::includeModule($moduleId)) {
    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
    echo BeginNote() . 'Модуль otus.marketdemo не установлен.' . EndNote();
    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
    return;
}

if ($APPLICATION->GetGroupRight($moduleId) < 'R') {
    $APPLICATION->AuthForm('Нет доступа к списку демо-записей.');
}

$APPLICATION->SetTitle('Демо-записи');

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

echo '<h1>Демо-записи</h1>';

$items = DemoItemTable::getList([
    'select' => ['ID', 'TITLE', 'STATUS', 'DATE_PUBLISH', 'BODY'],
    'order' => ['ID' => 'DESC'],
])->fetchAll();

if (empty($items)) {
    echo '<p>Записей пока нет.</p>';
} else {
    echo '<pre>';
    foreach ($items as $item) {
        $date = $item['DATE_PUBLISH'] instanceof \Bitrix\Main\Type\Date
            ? $item['DATE_PUBLISH']->format('d.m.Y')
            : (string)$item['DATE_PUBLISH'];

        echo sprintf(
            "#%d | %s | %s | %s\n",
            (int)$item['ID'],
            $item['TITLE'],
            $item['STATUS'],
            $date
        );
    }
    echo '</pre>';
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
