<?php

namespace Otus\Marketdemo\Event;

use Bitrix\Main\Config\Option;

class UserHandler
{
    public static function onAfterUserUpdate(array &$arFields): void
    {
        if (empty($arFields['ID'])) {
            return;
        }

        if (Option::get('otus.marketdemo', 'enable_log', 'N') !== 'Y') {
            return;
        }

        AddMessage2Log(
            sprintf('[otus.marketdemo] OnAfterUserUpdate: user ID=%d', (int)$arFields['ID']),
            'otus.marketdemo'
        );
    }
}
