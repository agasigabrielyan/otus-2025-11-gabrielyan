<?php

require_once __DIR__ . '/lib/TenantStorage.php';

$memberId = (string)($_REQUEST['member_id'] ?? '');
$authId = (string)($_REQUEST['AUTH_ID'] ?? '');
$refreshId = (string)($_REQUEST['REFRESH_ID'] ?? '');
$domain = (string)($_REQUEST['DOMAIN'] ?? '');

if ($memberId === '') {
    http_response_code(400);
    echo 'member_id is required';
    exit;
}

$storage = new TenantStorage(__DIR__ . '/data');
$storage->save($memberId, [
    'member_id' => $memberId,
    'domain' => $domain,
    'auth_id' => $authId,
    'refresh_id' => $refreshId,
    'installed_at' => date('c'),
]);

http_response_code(200);
echo 'install ok';