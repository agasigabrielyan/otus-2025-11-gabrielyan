<?php

/**
 * Шаг 6: result_modifier — меняем $arResult ПЕРЕД template.php.
 * Шаблон компонента (template.php) не трогаем — он остаётся в модуле.
 *
 * Проверка: /marketdemo/ — у заголовков записей появится префикс «★».
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

foreach ($arResult['ITEMS'] as &$item) {
    $item['TITLE'] = '★ ' . ($item['TITLE'] ?? '');
}
unset($item);

$arResult['MODIFIER_NOTE'] = 'Список дополнен в result_modifier.php (шаг 6)';
