<?php require ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
/**
 * @var $APPLICATION
 */
?>
<?php
    $APPLICATION->IncludeComponent(
        'Devconsult:Books',
        '.default',
        [],
        false
    );
?>
<?php require ($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>
