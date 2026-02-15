<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */

$currency = $arResult['CURRENCY'] ?? [];
$amount = number_format((float)$currency['AMOUNT'], 4, '.', ' ');

$date = '';
if (!empty($currency['DATE_UPDATE']) && $currency['DATE_UPDATE'] instanceof \Bitrix\Main\Type\DateTime) {
    $date = $currency['DATE_UPDATE']->format('d.m.Y H:i');
}
?>
<h2>Выбранная валюта</h2>
<table class="bordered" style="width: 50%;">
    <tr>
        <td>
            <?= htmlspecialcharsbx($currency['FULL_NAME']); ?>
            <small style="color:#888;">(<?= htmlspecialcharsbx($currency['CURRENCY']); ?>)</small>
        </td>

        <td>
            <?= $currency['FORMAT'] ? str_replace('#', $amount, $currency['FORMAT']) : $amount; ?>
        </td>

        <td>
            <time datetime="<?= $currency['DATE_UPDATE'] ?>">
                <?= $date ?: '—'; ?>
            </time>
        </td>
    </tr>
</table>
