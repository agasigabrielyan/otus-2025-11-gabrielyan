<?php

use Agasicompany\Periodlock\PeriodLockTable;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetTitle('Period lock — тест');

$rows = [];
$result = PeriodLockTable::getList([
    'order' => ['YEAR' => 'DESC', 'ENTITY_CODE' => 'ASC'],
]);

while ($row = $result->fetch()) {
    $rows[] = $row;
}
?>

<style>
    .period-lock-page { max-width: 1200px; margin: 24px auto; }
    .period-lock-page h1 { margin-bottom: 8px; }
    .period-lock-page .meta { color: #57606a; margin-bottom: 20px; font-size: 14px; }
    .period-lock-data { width: 100%; border-collapse: collapse; }
    .period-lock-data th,
    .period-lock-data td {
        border: 1px solid #d0d7de;
        padding: 10px 12px;
        text-align: left;
        font-size: 14px;
    }
    .period-lock-data th { background: #f6f8fa; }
    .period-lock-data tr:nth-child(even) td { background: #fafbfc; }
    .period-lock-empty {
        padding: 24px;
        border: 1px dashed #d0d7de;
        border-radius: 8px;
        color: #57606a;
        text-align: center;
    }
    .period-lock-locked-y { color: #b42318; font-weight: 600; }
    .period-lock-locked-n { color: #1a7f37; }
</style>

<div class="period-lock-page">
    <h1>Блокировка периодов</h1>
    <p class="meta">
        Таблица: <strong><?= htmlspecialcharsbx(PeriodLockTable::getTableName()) ?></strong>
        · записей: <strong><?= count($rows) ?></strong>
    </p>

    <?php if (empty($rows)): ?>
        <p class="period-lock-empty">Данных пока нет. Таблица в БД создана (lazy migration), записи появятся после добавления.</p>
    <?php else: ?>
        <table class="period-lock-data">
            <thead>
            <tr>
                <th>ID</th>
                <th>ENTITY_CODE</th>
                <th>YEAR</th>
                <th>IS_LOCKED</th>
                <th>LOCKED_BY</th>
                <th>LOCKED_AT</th>
                <th>UNLOCKED_BY</th>
                <th>UNLOCKED_AT</th>
                <th>CREATED_AT</th>
                <th>UPDATED_AT</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= (int)$row['ID'] ?></td>
                    <td><?= htmlspecialcharsbx($row['ENTITY_CODE']) ?></td>
                    <td><?= (int)$row['YEAR'] ?></td>
                    <td class="<?= $row['IS_LOCKED'] === 'Y' ? 'period-lock-locked-y' : 'period-lock-locked-n' ?>">
                        <?= htmlspecialcharsbx($row['IS_LOCKED']) ?>
                    </td>
                    <td><?= $row['LOCKED_BY'] ? (int)$row['LOCKED_BY'] : '—' ?></td>
                    <td><?= $row['LOCKED_AT'] ? htmlspecialcharsbx((string)$row['LOCKED_AT']) : '—' ?></td>
                    <td><?= $row['UNLOCKED_BY'] ? (int)$row['UNLOCKED_BY'] : '—' ?></td>
                    <td><?= $row['UNLOCKED_AT'] ? htmlspecialcharsbx((string)$row['UNLOCKED_AT']) : '—' ?></td>
                    <td><?= $row['CREATED_AT'] ? htmlspecialcharsbx((string)$row['CREATED_AT']) : '—' ?></td>
                    <td><?= $row['UPDATED_AT'] ? htmlspecialcharsbx((string)$row['UPDATED_AT']) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
