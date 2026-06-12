<?php

/**
 * Ленивое создание UF-поля «Светофор» на контактах CRM (CRM_CONTACT).
 * Срабатывает на OnProlog, если поля ещё нет в b_user_field.
 */

use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!Loader::includeModule('main')) {
    return;
}

$entityId = 'CRM_CONTACT';
$fieldName = 'UF_CRM_CONTACT_TRAFFIC_LIGHT';
$userTypeId = 'otus_traffic_light';

$exists = CUserTypeEntity::GetList(
    [],
    [
        'ENTITY_ID' => $entityId,
        'FIELD_NAME' => $fieldName,
    ]
)->Fetch();

if ($exists) {
    return;
}

$userType = new CUserTypeEntity();
$userType->Add([
    'ENTITY_ID' => $entityId,
    'FIELD_NAME' => $fieldName,
    'USER_TYPE_ID' => $userTypeId,
    'XML_ID' => $fieldName,
    'SORT' => 100,
    'MULTIPLE' => 'N',
    'MANDATORY' => 'N',
    'SHOW_FILTER' => 'N',
    'EDIT_FORM_LABEL' => ['ru' => 'Светофор', 'en' => 'Traffic light'],
    'LIST_COLUMN_LABEL' => ['ru' => 'Светофор', 'en' => 'Traffic light'],
    'LIST_FILTER_LABEL' => ['ru' => 'Светофор', 'en' => 'Traffic light'],
]);
