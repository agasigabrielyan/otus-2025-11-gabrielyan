<?php

declare(strict_types=1);

function marketappJsonError(int $code, string $error, array $extra = []): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['ok' => false, 'error' => $error], $extra), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

register_shutdown_function(static function (): void {
    $error = error_get_last();
    if ($error === null) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
    if (!in_array($error['type'], $fatalTypes, true)) {
        return;
    }

    if (headers_sent()) {
        return;
    }

    marketappJsonError(500, 'php_fatal', [
        'message' => $error['message'],
        'file' => $error['file'],
        'line' => $error['line'],
    ]);
});

require_once __DIR__ . '/lib/TenantStorage.php';

function callBitrixRest(string $domain, string $authId, string $method, array $params = []): array
{
    if (!ini_get('allow_url_fopen')) {
        return [
            'data' => [
                'error' => 'ALLOW_URL_FOPEN_OFF',
                'error_description' => 'Enable allow_url_fopen in php.ini or install php-curl',
            ],
        ];
    }

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
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);

    $raw = @file_get_contents($url, false, $context);
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

try {
    $memberId = (string)($_REQUEST['member_id'] ?? '');
    if ($memberId === '') {
        marketappJsonError(400, 'member_id is required');
    }

    $storage = new TenantStorage(__DIR__ . '/data');
    $tenant = $storage->load($memberId);

    if ($tenant === []) {
        marketappJsonError(404, 'tenant not found');
    }

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
        marketappJsonError(500, 'tenant has no domain or auth_id');
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
} catch (Throwable $e) {
    marketappJsonError(500, 'exception', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
}
