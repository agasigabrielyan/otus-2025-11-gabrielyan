<?php

require_once __DIR__ . '/lib/TenantStorage.php';

$memberId = (string)($_REQUEST['member_id'] ?? '');
if ($memberId === '') {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'member_id is required'], JSON_UNESCAPED_UNICODE);
    exit;
}

$storage = new TenantStorage(__DIR__ . '/data');
$tenant = $storage->load($memberId);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'ok' => true,
    'member_id' => $memberId,
    'tenant_found' => !empty($tenant),
    'tenant_data' => $tenant,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);