<?php

/**
 * Пример регистрации placement (запускать ОДИН РАЗ вручную, не из init.php).
 *
 * Нужно:
 * 1) Локальное REST-приложение в Bitrix (Настройки → Разработчикам → Другое → Локальное приложение)
 * 2) Подключить CRest (bitrix/rest или свой SDK)
 * 3) Подставить CLIENT_ID / CLIENT_SECRET или webhook
 * 4) Открыть этот файл в браузере под админом ИЛИ выполнить код из консоли/скрипта установки
 *
 * Документация: dev.1c-bitrix.ru → REST → placement.bind
 */

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!$USER->IsAdmin()) {
    die('Только для администратора');
}

$handlerUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/local/practice/ui-customization/rest/placement_handler.php';

// Пример вызова (раскомментируйте, когда подключён CRest):
/*
$result = CRest::call('placement.bind', [
    'PLACEMENT' => 'CRM_DEAL_LIST_MENU',
    'HANDLER' => $handlerUrl,
    'TITLE' => 'Otus: передать в 1С',
]);

echo '<pre>';
print_r($result);
echo '</pre>';
*/

echo '<h1>placement.bind — пример</h1>';
echo '<p>HANDLER URL:</p>';
echo '<code>' . htmlspecialcharsbx($handlerUrl) . '</code>';
echo '<h2>Код для лекции</h2>';
echo '<pre>' . htmlspecialcharsbx(<<<'PHP'
CRest::call('placement.bind', [
    'PLACEMENT' => 'CRM_DEAL_LIST_MENU',
    'HANDLER' => 'https://ВАШ_ДОМЕН/local/practice/ui-customization/rest/placement_handler.php',
    'TITLE' => 'Otus: передать в 1С',
]);
PHP) . '</pre>';
echo '<p>После bind пункт появится в контекстном меню списка сделок CRM.</p>';
