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

    $repository = new TenantRepository();
    $repository->ensureTable();

    $input = TenantAuth::getInput();
    $auth = TenantAuth::fromRequest($input);

    InstallLog::write('install.php called', [
        'event' => $input['event'] ?? null,
        'member_id' => $auth['member_id'],
        'has_auth_id' => $auth['auth_id'] !== '',
        'keys' => array_keys($input),
    ]);

    if ($auth['member_id'] === '') {
        // Проверка доступности URL из кабинета партнёра (GET без параметров)
        if ($method === 'GET' && ($input['event'] ?? '') !== 'ONAPPINSTALL') {
            http_response_code(200);
            echo 'ok';
            exit;
        }

        http_response_code(400);
        echo 'error: member_id is required';
        exit;
    }

    if (!TenantAuth::canSave($auth)) {
        http_response_code(400);
        echo 'error: auth_id is required';
        exit;
    }

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
