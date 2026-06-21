<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

try {
    AppConfig::load();

    $repository = new TenantRepository();
    $repository->ensureTable();

    $memberId = trim((string)($_REQUEST['member_id'] ?? ''));
    if ($memberId === '') {
        http_response_code(400);
        echo 'error: member_id is required';
        exit;
    }

    $repository->save($memberId, [
        'domain' => (string)($_REQUEST['DOMAIN'] ?? ''),
        'auth_id' => (string)($_REQUEST['AUTH_ID'] ?? ''),
        'refresh_id' => (string)($_REQUEST['REFRESH_ID'] ?? ''),
        'auth_expires_at' => (int)($_REQUEST['AUTH_EXPIRES'] ?? 0) ?: null,
    ]);

    http_response_code(200);
    echo 'install ok';
} catch (Throwable $e) {
    http_response_code(500);
    echo 'error: ' . $e->getMessage();
}
