<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */

$currency = $arResult['CURRENCY'] ?? [];
$amount = number_format((float)$currency['AMOUNT'], 4, '.', ' ');

$date = '';
if (!empty($currency['DATE_UPDATE']) && $currency['DATE_UPDATE'] instanceof \Bitrix\Main\Type\DateTime) {
    $date = $currency['DATE_UPDATE']->format('d.m.Y H:i');
}
?>

<div>
    <div class="mb-3">Репозиторий: <a target="_blank" href="https://github.com/agasigabrielyan/otus-2025-11-gabrielyan">https://github.com/agasigabrielyan/otus-2025-11-gabrielyan</a></div>
    <div class="mb-3">Класс компонента:
        <a
                target="_blank"
                href="/bitrix/admin/fileman_file_edit.php?path=%2Flocal%2Fcomponents%2FOtus%2FOtusCurrencies%2Fclass.php&full_src=Y&site=s1&lang=ru&&filter=Y&set_filter=Y">
            /bitrix/admin/fileman_file_edit.php?path=%2Flocal%2Fcomponents%2FOtus%2FOtusCurrencies%2Fclass.php&full_src=Y&site=s1&lang=ru&&filter=Y&set_filter=Y
        </a>
    </div>
</div>

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
                <b>
                    <?= $date ?: '—'; ?>
                </b>
            </time>
        </td>
    </tr>
</table>
