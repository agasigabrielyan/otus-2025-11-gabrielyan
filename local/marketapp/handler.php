<?php

require_once __DIR__ . '/lib/TenantStorage.php';

function callBitrixRest(string $domain, string $authId, string $method, array $params = []): array
{
    $url = 'https://' . $domain . '/rest/' . $method . '.json';
    $params['auth'] = $authId;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($params),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ]);

    $raw = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode((string)$raw, true);

    return [
        'http_code' => $httpCode,
        'data' => is_array($data) ? $data : ['raw' => $raw],
    ];
}

$memberId = (string)($_REQUEST['member_id'] ?? '');
if ($memberId === '') {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'member_id is required'], JSON_UNESCAPED_UNICODE);
    exit;
}

$storage = new TenantStorage(__DIR__ . '/data');
$tenant = $storage->load($memberId);

if (empty($tenant)) {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'tenant not found'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Bitrix при открытии handler передаёт свежий AUTH_ID — обновляем storage
$requestAuthId = (string)($_REQUEST['AUTH_ID'] ?? '');
if ($requestAuthId !== '') {
    $tenant['auth_id'] = $requestAuthId;
    if (!empty($_REQUEST['REFRESH_ID'])) {
        $tenant['refresh_id'] = (string)$_REQUEST['REFRESH_ID'];
    }
    if (!empty($_REQUEST['DOMAIN'])) {
        $tenant['domain'] = (string)$_REQUEST['DOMAIN'];
    }
    $storage->save($memberId, $tenant);
}

$domain = (string)($tenant['domain'] ?? '');
$authId = (string)($tenant['auth_id'] ?? '');

if ($domain === '' || $authId === '') {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'tenant has no domain or auth_id'], JSON_UNESCAPED_UNICODE);
    exit;
}

$rest = callBitrixRest($domain, $authId, 'crm.deal.list', [
    'select' => ['ID', 'TITLE', 'STAGE_ID'],
    'order' => ['ID' => 'DESC'],
    'start' => 0,
]);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'ok' => empty($rest['data']['error']),
    'member_id' => $memberId,
    'domain' => $domain,
    'deals' => $rest['data']['result'] ?? [],
    'rest_error' => $rest['data']['error'] ?? null,
    'rest_error_description' => $rest['data']['error_description'] ?? null,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
