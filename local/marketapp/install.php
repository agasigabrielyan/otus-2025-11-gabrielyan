<?php

declare(strict_types=1);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Bitrix24 проверяет URL запросом HEAD (ждёт код 200)
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

    InstallLog::write('install.php called', [
        'event' => $event !== '' ? $event : null,
        'member_id' => $auth['member_id'],
        'has_auth_id' => $auth['auth_id'] !== '',
    ]);

    // Проверка URL из кабинета partners (GET/HEAD без параметров)
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

    // Не установка — открыли install.php вместо index.php, перенаправляем
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

    InstallLog::write('install.php saved', [
        'member_id' => $auth['member_id'],
        'rows' => $repository->countAll(),
    ]);

    http_response_code(200);
    echo 'install ok';
} catch (Throwable $e) {
    InstallLog::write('install.php error', ['message' => $e->getMessage()]);
    http_response_code(500);
    echo 'error: ' . $e->getMessage();
}
