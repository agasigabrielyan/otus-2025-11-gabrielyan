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
            'EDIT_CALLBACK' => [__CLASS__, 'GetEditFormHTML'],
            'VIEW_CALLBACK' => [__CLASS__, 'GetViewHTML'],
        ];
    }

    public static function GetDbColumnType(): string
    {
        return 'varchar(10)';
    }

    public static function GetEditFormHTML(array $userField, array $htmlControl, bool $bVarsFromForm = false): string
    {
        $value = $htmlControl['VALUE'] ?? $userField['VALUE'] ?? '';
        if (is_array($value)) {
            $value = reset($value) ?: '';
        }
        $value = (string)$value;

        $name = (string)($htmlControl['NAME'] ?? $userField['FIELD_NAME'] ?? '');

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
        $value = $htmlControl['VALUE'] ?? $userField['VALUE'] ?? '';
        if (is_array($value)) {
            $value = reset($value) ?: '';
        }
        $value = (string)$value;

        $color = in_array($value, ['green', 'yellow', 'red'], true) ? $value : '#ccc';

        return '<span class="fields enumeration">'
            . '<span style="display:inline-block;width:16px;height:16px;border-radius:50%;'
            . 'background:' . htmlspecialcharsbx($color) . ';vertical-align:middle;margin-right:6px;"></span>'
            . htmlspecialcharsbx($value !== '' ? $value : '—')
            . '</span>';
    }
}
