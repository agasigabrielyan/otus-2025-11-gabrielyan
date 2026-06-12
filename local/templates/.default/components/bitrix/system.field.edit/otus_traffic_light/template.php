<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/Currentcompany/ui-customization/uf_type/OtusTrafficLightType.php';

$userField = $arParams['arUserField'] ?? [];
$values = $arResult['VALUE'] ?? [''];

foreach ($values as $value) {
    echo \Currentcompany\UiCustomization\UfType\OtusTrafficLightType::GetEditFormHTML(
        $userField,
        [
            'NAME' => $userField['FIELD_NAME'] ?? '',
            'VALUE' => $value,
        ]
    );
}
