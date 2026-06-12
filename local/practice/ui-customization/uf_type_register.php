<?php

/**
 * Регистрация своего типа UF (шаг 5).
 * Подключается из local/init.php — не на каждой странице портала, а при построении списка типов UF.
 */

use Otus\UiCustomization\UfType\OtusWidgetType;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$ufClassFile = __DIR__ . '/uf_type/OtusWidgetType.php';
if (!is_file($ufClassFile)) {
    return;
}

require_once $ufClassFile;

return OtusWidgetType::GetUserTypeDescription();
