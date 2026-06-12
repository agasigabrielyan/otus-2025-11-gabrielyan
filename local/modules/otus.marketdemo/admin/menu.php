<?php

global $APPLICATION;

$moduleId = 'otus.marketdemo';

if ($APPLICATION->GetGroupRight($moduleId) < 'R') {
    return [];
}

return [
    'parent' => 'global_menu_services',
    'section' => 'otus_marketdemo',
    'sort' => 1000,
    'text' => 'Демо Marketplace',
    'title' => 'Учебный модуль Otus Marketplace',
    'items_id' => 'menu_otus_marketdemo',
    'items' => [
        [
            'text' => 'Демо-записи',
            'url' => 'otus_marketdemo_list.php?lang=' . LANGUAGE_ID,
            'more_url' => [
                'otus_marketdemo_list.php',
            ],
            'title' => 'Список демо-записей',
        ],
    ],
];
