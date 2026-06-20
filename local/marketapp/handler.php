<?php

require_once __DIR__ . '/lib/TenantStorage.php';

function callBitrixRest(string $domain, string $authId, string $method, array $params = []): array
{
    $url = 'https://' . $domain . '/rest/' . $method . '.json';
    $params['auth'] = $authId;

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($params),
            'timeout' => 20,
            'ignore_errors' => true,
        ],
    ]);

    $raw = file_get_contents($url, false, $context);
    if ($raw === false) {
        return [
            'data' => [
                'error' => 'HTTP_REQUEST_FAILED',
                'error_description' => 'Cannot call Bitrix REST from server',
            ],
        ];
    }

    $data = json_decode($raw, true);

    return [
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
