<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetTitle('Демо Marketplace');

$APPLICATION->IncludeComponent(
    'otus:marketdemo.list',
    '',
    [
        'SHOW_EXPIRED' => 'N',
    ],
    false
);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
