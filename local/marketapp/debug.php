<?php

declare(strict_types=1);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/lib/bootstrap.php';
FrameHeaders::allowBitrix24();

function tenantForRest(?array $dbTenant, array $auth, string $memberId): array
{
    return [
        'MEMBER_ID' => $memberId,
        'DOMAIN' => (string)($auth['domain'] ?: ($dbTenant['DOMAIN'] ?? '')),
        'AUTH_ID' => (string)($auth['auth_id'] ?: ($dbTenant['AUTH_ID'] ?? '')),
        'REFRESH_ID' => (string)($auth['refresh_id'] ?: ($dbTenant['REFRESH_ID'] ?? '')),
    ];
}

$lines = [];

try {
    AppConfig::load();

    $repository = new TenantRepository();
    $repository->ensureTable();

    $input = TenantAuth::getInput();
    $auth = TenantAuth::fromRequest($input);

    if (TenantAuth::canSave($auth)) {
        $repository->save($auth['member_id'], $auth);
    }

    $memberId = $auth['member_id'];
    if ($memberId === '' && !empty($input['member_id'])) {
        $memberId = trim((string)$input['member_id']);
    }

    $dbTenant = $memberId !== '' ? $repository->load($memberId) : null;

    if ($dbTenant === null) {
        throw new RuntimeException('Откройте debug.php из приложения на портале Bitrix24.');
    }

    $tenant = tenantForRest($dbTenant, $auth, $memberId);
    $rest = new BitrixRest();

    $lines[] = 'member_id: ' . $memberId;
    $lines[] = 'domain: ' . $tenant['DOMAIN'];
    $lines[] = 'APPLICATION_SCOPE: ' . (string)($input['APPLICATION_SCOPE'] ?? $input['auth']['scope'] ?? '—');

    $scopeCheck = $rest->callRaw($tenant, 'scope', [], $repository);
    $lines[] = 'scope method: ' . json_encode($scopeCheck, JSON_UNESCAPED_UNICODE);

    $available = $rest->callRaw($tenant, 'placement.list', [], $repository);
    $lines[] = 'placement.list: ' . json_encode($available, JSON_UNESCAPED_UNICODE);

    $registered = $rest->callRaw($tenant, 'placement.get', [], $repository);
    $lines[] = 'placement.get (до bind): ' . json_encode($registered, JSON_UNESCAPED_UNICODE);

    $handler = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http')
        . '://' . ($_SERVER['HTTP_HOST'] ?? 'otusgabrielyan.ru')
        . '/local/marketapp/widget.php';

    foreach (['CRM_DEAL_DETAIL_TAB', 'CRM_DEAL_LIST_TOOLBAR'] as $placement) {
        $bind = $rest->callRaw($tenant, 'placement.bind', [
            'PLACEMENT' => $placement,
            'HANDLER' => $handler,
            'TITLE' => 'CRM-счётчик',
            'LANG_ALL' => json_encode([
                'ru' => ['TITLE' => 'CRM-счётчик'],
            ], JSON_UNESCAPED_UNICODE),
        ], $repository);
        $lines[] = 'placement.bind ' . $placement . ': ' . json_encode($bind, JSON_UNESCAPED_UNICODE);
    }

    $registeredAfter = $rest->callRaw($tenant, 'placement.get', [], $repository);
    $lines[] = 'placement.get (после bind): ' . json_encode($registeredAfter, JSON_UNESCAPED_UNICODE);

    InstallLog::write('debug.php done', ['member_id' => $memberId]);
} catch (Throwable $e) {
    $lines[] = 'ERROR: ' . $e->getMessage();
    InstallLog::write('debug.php error', ['message' => $e->getMessage()]);
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Debug виджета</title>
    <style>
        body { font-family: monospace; padding: 16px; white-space: pre-wrap; font-size: 13px; }
        .hint { font-family: Arial, sans-serif; margin-bottom: 16px; }
    </style>
</head>
<body>
<p class="hint">Откройте эту страницу из приложения на портале. Скопируйте вывод, если виджет не появился.</p>
<?php foreach ($lines as $line): ?>
<?= htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\n" ?>
<?php endforeach; ?>
<p class="hint"><a href="index.php">← Назад</a></p>
</body>
</html>
