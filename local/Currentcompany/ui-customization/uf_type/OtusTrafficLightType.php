<?php

namespace Currentcompany\UiCustomization\UfType;

class OtusTrafficLightType
{
    public static function GetUserTypeDescription(): array
    {
        return [
            'USER_TYPE_ID' => 'otus_traffic_light',
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => 'Field as traffic light',
            'BASE_TYPE' => 'string',
            'USE_FIELD_COMPONENT' => 'N',
            'EDIT_CALLBACK' => [__CLASS__, 'getPublicEdit'],
            'VIEW_CALLBACK' => [__CLASS__, 'getPublicView'],
        ];
    }

    public static function getPublicEdit(array $userField, array $additionalParameters = []): string
    {
        return self::GetEditFormHTML($userField, self::prepareHtmlControl($userField, $additionalParameters));
    }

    public static function getPublicView(array $userField, array $additionalParameters = []): string
    {
        return self::GetViewHTML($userField, self::prepareHtmlControl($userField, $additionalParameters));
    }

    public static function GetDbColumnType(): string
    {
        return 'varchar(10)';
    }

    public static function GetEditFormHTML(array $userField, array $htmlControl, bool $bVarsFromForm = false): string
    {
        $htmlControl = self::prepareHtmlControl($userField, $htmlControl);
        $value = $htmlControl['VALUE'];
        $name = $htmlControl['NAME'];

        $options = [
            'green' => 'Low',
            'yellow' => 'Middle',
            'red' => 'High',
        ];

        $html = '<div class="fields string" id="main_' . htmlspecialcharsbx($name) . '">';
        $html .= '<select class="bx-user-field-enum fields enumeration"'
            . ' name="' . htmlspecialcharsbx($name) . '"'
            . ' onchange="BX.fireEvent(this, \'bxchange\');">';
        $html .= '<option value="">' . htmlspecialcharsbx('—') . '</option>';

        foreach ($options as $code => $label) {
            $selected = ($value === $code) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialcharsbx($code) . '"' . $selected . '>'
                . htmlspecialcharsbx($label) . '</option>';
        }

        $html .= '</select></div>';

        return $html;
    }

    public static function GetViewHTML(array $userField, array $htmlControl): string
    {
        $htmlControl = self::prepareHtmlControl($userField, $htmlControl);
        $value = $htmlControl['VALUE'];
        $color = in_array($value, ['green', 'yellow', 'red'], true) ? $value : '#ccc';

        return '<span class="fields enumeration">'
            . '<span style="display:inline-block;width:16px;height:16px;border-radius:50%;'
            . 'background:' . htmlspecialcharsbx($color) . ';vertical-align:middle;margin-right:6px;"></span>'
            . htmlspecialcharsbx($value !== '' ? $value : '—')
            . '</span>';
    }

    private static function prepareHtmlControl(array $userField, array $htmlControl): array
    {
        $value = $htmlControl['VALUE'] ?? $userField['VALUE'] ?? '';
        if (is_array($value)) {
            $value = reset($value) ?: '';
        }

        $value = (string)$value;
        if ($value !== '' && str_contains($value, '&')) {
            $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML401, defined('LANG_CHARSET') ? LANG_CHARSET : 'UTF-8');
        }

        $name = (string)($htmlControl['NAME'] ?? $userField['FIELD_NAME'] ?? '');

        return [
            'NAME' => $name,
            'VALUE' => $value,
        ];
    }
}
