<?php

declare(strict_types=1);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/lib/bootstrap.php';
FrameHeaders::allowBitrix24();

$messages = [];
$errors = [];

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
        throw new RuntimeException('Откройте эту страницу из приложения на портале Bitrix24.');
    }

    $tenant = [
        'MEMBER_ID' => $memberId,
        'DOMAIN' => (string)($auth['domain'] ?: $dbTenant['DOMAIN']),
        'AUTH_ID' => (string)($auth['auth_id'] ?: $dbTenant['AUTH_ID']),
        'REFRESH_ID' => (string)($auth['refresh_id'] ?: $dbTenant['REFRESH_ID']),
    ];

    InstallLog::write('rebind.php start', [
        'member_id' => $memberId,
        'scope' => (string)($input['APPLICATION_SCOPE'] ?? ''),
    ]);

    (new PlacementInstaller())->install($tenant, $repository);
    $messages[] = 'Виджеты зарегистрированы: CRM_DEAL_LIST_TOOLBAR, CRM_DEAL_DETAIL_TAB';

    InstallLog::write('rebind.php placement bound', ['member_id' => $memberId]);

    $rest = new BitrixRest();
    $registered = $rest->getPlacements($tenant, $repository);
    $messages[] = 'placement.get: ' . json_encode($registered, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    $errors[] = $e->getMessage();
    InstallLog::write('rebind.php error', ['message' => $e->getMessage()]);
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Регистрация виджета</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 16px; }
        .ok { color: #1eae58; }
        .err { color: #c60404; }
        pre { background: #f5f5f5; padding: 10px; overflow: auto; }
    </style>
</head>
<body>
<h1>Регистрация виджета</h1>
<?php foreach ($messages as $message): ?>
    <pre class="ok"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></pre>
<?php endforeach; ?>
<?php foreach ($errors as $error): ?>
    <p class="err"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <p class="err">Если ошибка про scope/placement — удалите приложение на портале и установите заново после включения галочки «Встраивать виджеты».</p>
<?php endforeach; ?>
<p><a href="index.php">← Назад в приложение</a> · <a href="debug.php">Диагностика</a></p>
</body>
</html>
