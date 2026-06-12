<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

require_once __DIR__ . "/uf_type/OtusTrafficLightType.php";

return \Currentcompany\UiCustomization\UfType\OtusTrafficLightType::GetUserTypeDescription();