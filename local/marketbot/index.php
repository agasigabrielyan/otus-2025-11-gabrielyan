<?php

declare(strict_types=1);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/lib/bootstrap.php';

$status = 'Не подключено';
$domain = '—';
$memberId = '—';
$error = '';

try {
    AppConfig::load();

    $repository = new TenantRepository();
    $repository->ensureTable();

    $input = TenantAuth::getInput();
    $auth = TenantAuth::fromRequest($input);

    if (TenantAuth::canSave($auth)) {
        $repository->save($auth['member_id'], $auth);
        $status = 'Токены сохранены';
    }

    $currentMemberId = $auth['member_id'];
    if ($currentMemberId === '' && !empty($input['member_id'])) {
        $currentMemberId = trim((string)$input['member_id']);
    }

    $tenant = $currentMemberId !== '' ? $repository->load($currentMemberId) : null;

    if ($tenant !== null) {
        $status = 'Подключено к порталу';
        $domain = (string)($tenant['DOMAIN'] ?? '—');
        $memberId = (string)($tenant['MEMBER_ID'] ?? '—');
    }
} catch (Throwable $e) {
    $error = $e->getMessage();
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>CRM-статистика</title>
    <style>
        body { font-family: sans-serif; margin: 24px; color: #333; }
        .card { border: 1px solid #e0e0e0; border-radius: 8px; padding: 16px; max-width: 480px; }
        .ok { color: #2e7d32; }
        .err { color: #c62828; }
    </style>
</head>
<body>
<h1>CRM-статистика в чате</h1>
<div class="card">
    <p>Статус: <strong class="<?= $error === '' ? 'ok' : 'err' ?>"><?= htmlspecialchars($error !== '' ? $error : $status, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong></p>
    <p>Портал: <?= htmlspecialchars($domain, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <p>member_id: <?= htmlspecialchars($memberId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
</div>
<p>Чат-бот подключим на следующем шаге.</p>
</body>
</html>
