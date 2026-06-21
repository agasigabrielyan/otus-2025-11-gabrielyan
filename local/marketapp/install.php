<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

try {
    AppConfig::load();

    $repository = new TenantRepository();
    $repository->ensureTable();

    $auth = TenantAuth::fromRequest($_REQUEST);

    if ($auth['member_id'] === '') {
        http_response_code(400);
        echo 'error: member_id is required';
        exit;
    }

    $repository->save($auth['member_id'], $auth);

    http_response_code(200);
    echo 'install ok';
} catch (Throwable $e) {
    http_response_code(500);
    echo 'error: ' . $e->getMessage();
}
