<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

$module_id = 'otus.marketdemo';

Loc::loadMessages(__FILE__);

if (!Bitrix\Main\Loader::includeModule($module_id)) {
    return;
}

$MODULE_RIGHT = $APPLICATION->GetGroupRight($module_id);
if ($MODULE_RIGHT < 'W') {
    return;
}

require __DIR__ . '/default_options.php';

$aTabs = [
    [
        'DIV' => 'edit1',
        'TAB' => Loc::getMessage('OTUS_MARKETDEMO_TAB_MAIN'),
        'TITLE' => Loc::getMessage('OTUS_MARKETDEMO_TAB_MAIN_TITLE'),
    ],
    [
        'DIV' => 'edit2',
        'TAB' => Loc::getMessage('OTUS_MARKETDEMO_TAB_AGENT'),
        'TITLE' => Loc::getMessage('OTUS_MARKETDEMO_TAB_AGENT_TITLE'),
    ],
];

$tabControl = new CAdminTabControl('tabControl', $aTabs);

$agent_interval = (int)Option::get(
    $module_id,
    'agent_interval',
    $otus_marketdemo_default_option['agent_interval']
);
$default_status = Option::get(
    $module_id,
    'default_status',
    $otus_marketdemo_default_option['default_status']
);
$enable_log = Option::get(
    $module_id,
    'enable_log',
    $otus_marketdemo_default_option['enable_log']
);

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_REQUEST['Update'] ?? '') !== ''
    && check_bitrix_sessid()
) {
    $agent_interval = max(1, (int)($_REQUEST['agent_interval'] ?? $agent_interval));
    $default_status = in_array(
        (string)($_REQUEST['default_status'] ?? ''),
        ['active', 'expired'],
        true
    ) ? (string)$_REQUEST['default_status'] : $otus_marketdemo_default_option['default_status'];
    $enable_log = ($_REQUEST['enable_log'] ?? '') === 'Y' ? 'Y' : 'N';

    Option::set($module_id, 'agent_interval', (string)$agent_interval);
    Option::set($module_id, 'default_status', $default_status);
    Option::set($module_id, 'enable_log', $enable_log);

    LocalRedirect(
        $APPLICATION->GetCurPage()
        . '?mid=' . urlencode($module_id)
        . '&lang=' . LANGUAGE_ID
        . '&' . $tabControl->ActiveTabParam()
    );
}
?>

<form method="post" action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($module_id) ?>&lang=<?= LANGUAGE_ID ?>">
    <?= bitrix_sessid_post() ?>

    <?php
    $tabControl->Begin();

    $tabControl->BeginNextTab();
    ?>
    <tr>
        <td width="40%"><?= Loc::getMessage('OTUS_MARKETDEMO_OPT_DEFAULT_STATUS') ?>:</td>
        <td width="60%">
            <select name="default_status">
                <option value="active"<?= $default_status === 'active' ? ' selected' : '' ?>>
                    <?= Loc::getMessage('OTUS_MARKETDEMO_STATUS_ACTIVE') ?>
                </option>
                <option value="expired"<?= $default_status === 'expired' ? ' selected' : '' ?>>
                    <?= Loc::getMessage('OTUS_MARKETDEMO_STATUS_EXPIRED') ?>
                </option>
            </select>
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage('OTUS_MARKETDEMO_OPT_ENABLE_LOG') ?>:</td>
        <td>
            <input
                type="checkbox"
                name="enable_log"
                value="Y"
                <?= $enable_log === 'Y' ? ' checked' : '' ?>
            >
        </td>
    </tr>
    <?php
    $tabControl->EndTab();

    $tabControl->BeginNextTab();
    ?>
    <tr>
        <td width="40%"><?= Loc::getMessage('OTUS_MARKETDEMO_OPT_AGENT_INTERVAL') ?>:</td>
        <td width="60%">
            <input type="number" name="agent_interval" value="<?= (int)$agent_interval ?>" min="1" step="1">
            <br><small><?= Loc::getMessage('OTUS_MARKETDEMO_OPT_AGENT_INTERVAL_HINT') ?></small>
        </td>
    </tr>
    <?php
    $tabControl->EndTab();

    $tabControl->Buttons();
    $tabControl->End();
    ?>
</form>
