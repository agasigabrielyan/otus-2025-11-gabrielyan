<?php

declare(strict_types=1);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'HEAD') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/lib/bootstrap.php';

try {
    AppConfig::load();

    $input = TenantAuth::getInput();
    $auth = TenantAuth::fromRequest($input);
    $event = (string)($input['event'] ?? '');

    if ($auth['member_id'] === '') {
        if ($method === 'GET' && $event !== 'ONAPPINSTALL') {
            http_response_code(200);
            echo 'ok';
            exit;
        }

        http_response_code(400);
        echo 'error: member_id is required';
        exit;
    }

    if ($event !== 'ONAPPINSTALL') {
        $query = http_build_query($input);
        header('Location: index.php' . ($query !== '' ? '?' . $query : ''));
        exit;
    }

    if (!TenantAuth::canSave($auth)) {
        http_response_code(400);
        echo 'error: auth_id is required';
        exit;
    }

    $repository = new TenantRepository();
    $repository->ensureTable();
    $repository->save($auth['member_id'], $auth);

    http_response_code(200);
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="utf-8">
        <title>Установка</title>
        <script src="https://api.bitrix24.com/api/v1/"></script>
    </head>
    <body>
    <p>Установка завершена. Токены сохранены.</p>
    <script>
        BX24.init(function () {
            BX24.installFinish();
        });
    </script>
    </body>
    </html>
    <?php
} catch (Throwable $e) {
    http_response_code(500);
    echo 'error: ' . $e->getMessage();
}
