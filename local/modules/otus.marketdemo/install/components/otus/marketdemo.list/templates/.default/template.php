<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */
?>

<div class="otus-marketdemo-list">
    <h2>Демо-записи</h2>

    <?php if (!empty($arResult['MODIFIER_NOTE'])): ?>
        <p style="color:#2fc6f6;font-size:13px;"><?= htmlspecialcharsbx($arResult['MODIFIER_NOTE']) ?></p>
    <?php endif; ?>

    <?php if (empty($arResult['ITEMS'])): ?>
        <p>Записей нет.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($arResult['ITEMS'] as $item): ?>
                <li>
                    <strong><?= htmlspecialcharsbx($item['TITLE']) ?></strong>
                    <span>(<?= htmlspecialcharsbx($item['DATE_PUBLISH_FORMATTED']) ?>)</span>
                    <?php if (($item['STATUS'] ?? '') === 'expired'): ?>
                        <em> — просрочена</em>
                    <?php endif; ?>
                    <p><?= nl2br(htmlspecialcharsbx($item['BODY'])) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
