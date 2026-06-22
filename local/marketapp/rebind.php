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

    $tenant = $memberId !== '' ? $repository->load($memberId) : null;

    if ($tenant === null) {
        throw new RuntimeException('Откройте эту страницу из приложения на портале Bitrix24.');
    }

    $rest = new BitrixRest();

    InstallLog::write('rebind.php start', ['member_id' => $memberId]);

    (new PlacementInstaller())->install($tenant, $repository);
    $messages[] = 'Виджеты зарегистрированы: CRM_DEAL_LIST_TOOLBAR, CRM_DEAL_DETAIL_TAB';

    InstallLog::write('rebind.php placement bound', ['member_id' => $memberId]);

    $registered = $rest->getPlacements($tenant, $repository);
    if ($registered !== []) {
        $messages[] = 'Зарегистрировано handlers: ' . count($registered);
    }
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
    </style>
</head>
<body>
<h1>Регистрация виджета</h1>
<?php foreach ($messages as $message): ?>
    <p class="ok"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
<?php endforeach; ?>
<?php foreach ($errors as $error): ?>
    <p class="err"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
<?php endforeach; ?>
<p><a href="index.php">← Назад в приложение</a></p>
</body>
</html>
