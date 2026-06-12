<?php

namespace Otus\UiCustomization\UfType;

/**
 * Минимальный свой тип UF — виджет в карточке CRM (шаг 5).
 * После регистрации: Настройки CRM → Пользовательские поля → тип «Otus: виджет».
 */
class OtusWidgetType
{
    public static function GetUserTypeDescription(): array
    {
        return [
            'USER_TYPE_ID' => 'otus_widget',
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => 'Otus: виджет (демо)',
            'BASE_TYPE' => 'string',
        ];
    }

    public static function GetDbColumnType(): string
    {
        return 'text';
    }

    public static function GetEditFormHTML(array $userField, array $htmlControl): string
    {
        $value = (string)($htmlControl['VALUE'] ?? '');

        return '<div style="padding:8px;border:2px dashed #9dcf00;border-radius:6px;">'
            . '<strong>Otus widget (edit)</strong><br>'
            . '<input type="text" name="' . htmlspecialcharsbx($htmlControl['NAME']) . '"'
            . ' value="' . htmlspecialcharsbx($value) . '" style="width:100%;margin-top:6px;">'
            . '</div>';
    }

    public static function GetViewHTML(array $userField, array $htmlControl): string
    {
        $value = (string)($htmlControl['VALUE'] ?? '—');

        return '<div style="padding:8px;border:2px solid #2fc6f6;border-radius:6px;">'
            . '<strong>Otus widget (view)</strong>: ' . htmlspecialcharsbx($value)
            . '</div>';
    }
}
