<?php

declare(strict_types=1);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/lib/bootstrap.php';
FrameHeaders::allowBitrix24();

$dealCount = '—';
$error = '';

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
        $rest = new BitrixRest();
        $dealCount = (string)$rest->getDealCount($tenant, $repository);
    } else {
        $error = 'Портал не подключён';
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
    <title>CRM-счётчик</title>
    <script src="https://api.bitrix24.com/api/v1/"></script>
    <style>
        body { margin: 0; padding: 10px 12px; font-family: Arial, sans-serif; font-size: 13px; color: #333; }
        .widget { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; background: #fff; border: 1px solid #c6cdd3; border-radius: 8px; }
        .widget__label { color: #828b95; }
        .widget__value { font-size: 18px; font-weight: 600; color: #2fc6f6; }
        .widget__error { color: #c60404; }
    </style>
</head>
<body>
<?php if ($error !== ''): ?>
    <div class="widget widget__error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
<?php else: ?>
    <div class="widget">
        <span class="widget__label">Сделок в CRM:</span>
        <span class="widget__value"><?= htmlspecialchars($dealCount, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
    </div>
<?php endif; ?>
<script>
    BX24.init(function () {
        if (typeof BX24.fitWindow === 'function') {
            BX24.fitWindow();
        }
    });
</script>
</body>
</html>
