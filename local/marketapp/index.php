<?php

declare(strict_types=1);

// Bitrix24 проверяет URL запросом HEAD (ждёт код 200)
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/lib/bootstrap.php';
FrameHeaders::allowBitrix24();

$status = 'Не подключено к порталу';
$domain = '—';
$dealCount = '—';

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

    if ($tenant !== null) {
        $domain = (string)($tenant['DOMAIN'] ?? '—');
        $status = 'Подключено';

        $rest = new BitrixRest();
        $dealCount = (string)$rest->getDealCount($tenant, $repository);
    } elseif (TenantAuth::canSave($auth)) {
        $domain = $auth['domain'] !== '' ? $auth['domain'] : '—';
        $status = 'Токены получены, проверьте таблицу';
    }
} catch (Throwable $e) {
    $status = 'Ошибка: ' . $e->getMessage();
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CRM-счётчик</title>
    <style>
        body { margin: 0; padding: 16px; font-family: Arial, sans-serif; background: #f5f7fa; color: #333; }
        .app { max-width: 480px; margin: 0 auto; }
        .card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .label { font-size: 13px; color: #828b95; margin: 0 0 4px; }
        .value { font-size: 18px; margin: 0 0 16px; }
        .value_big { font-size: 28px; color: #2fc6f6; font-weight: 600; }
    </style>
</head>
<body>
<div class="app">
    <h1>CRM-счётчик</h1>
    <div class="card">
        <p class="label">Статус</p>
        <p class="value"><?= htmlspecialchars($status, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        <p class="label">Портал</p>
        <p class="value"><?= htmlspecialchars($domain, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        <p class="label">Сделок в CRM</p>
        <p class="value value_big"><?= htmlspecialchars($dealCount, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        <?php if ($status === 'Подключено'): ?>
            <p style="margin-top:16px;font-size:13px;">
                <a href="rebind.php">Зарегистрировать виджет в CRM</a>
            </p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
