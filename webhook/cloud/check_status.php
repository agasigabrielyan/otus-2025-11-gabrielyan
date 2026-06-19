<?php
$mapFile = $_SERVER['DOCUMENT_ROOT'] . '/webhook/cloud/status_map.json';
$dealId = (int)($_GET['deal_id'] ?? 0);

$map = [];
if (is_file($mapFile)) {
    $map = json_decode((string)file_get_contents($mapFile), true) ?: [];
}

$item = $dealId > 0 && isset($map[$dealId]) ? $map[$dealId] : null;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Проверка статуса сделки</title>
</head>
<body>
    <h1>Проверка статуса сделки</h1>

    <form method="get">
        <label>Deal ID:
            <input type="number" name="deal_id" value="<?= htmlspecialchars((string)$dealId) ?>" required>
        </label>
        <button type="submit">Проверить</button>
    </form>

    <hr>

    <?php if ($dealId <= 0): ?>
        <p>Введите ID сделки.</p>
    <?php elseif ($item): ?>
        <p><strong>Сделка:</strong> <?= htmlspecialchars((string)$item['title']) ?></p>
        <p><strong>Stage:</strong> <?= htmlspecialchars((string)$item['stage_id']) ?></p>
        <p><strong>Обновлено:</strong> <?= htmlspecialchars((string)$item['updated_at']) ?></p>
    <?php else: ?>
        <p>По сделке #<?= $dealId ?> данных пока нет (еще не приходило событие ONCRMDEALUPDATE).</p>
    <?php endif; ?>
</body>
</html>