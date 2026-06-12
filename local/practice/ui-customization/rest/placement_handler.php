<?php

/**
 * Страница-обработчик placement (открывается в iframe Bitrix24).
 * URL для placement.bind HANDLER:
 * https://ВАШ_ДОМЕН/local/practice/ui-customization/rest/placement_handler.php
 */

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

$placement = (string)($_REQUEST['PLACEMENT'] ?? '');
$placementOptions = $_REQUEST['PLACEMENT_OPTIONS'] ?? [];

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Otus placement</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 16px; margin: 0; }
        .box { border: 2px solid #2fc6f6; border-radius: 8px; padding: 12px; }
    </style>
</head>
<body>
<div class="box">
    <h3>REST placement — demo</h3>
    <p>Placement: <strong><?= htmlspecialcharsbx($placement) ?></strong></p>
    <p>Это страница в iframe. Сюда ведёт <code>HANDLER</code> из <code>placement.bind</code>.</p>
</div>
</body>
</html>
