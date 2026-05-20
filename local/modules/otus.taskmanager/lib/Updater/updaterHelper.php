<?php

namespace Otus\TaskManager\Updater;

use Bitrix\Main\Config\Option;

class UpdateHelper
{
    public static function addFileAttachmentProperty(): void
    {
        if(!\Bitrix\Main\Loader::includeModule("iblock")) {
            throw new \RuntimeException('Module iblock not installed');
        }

        $iblockId = (int)Option::get("otus.taskmanager", "IBLOCK_ID");

        if(!$iblockId) {
            throw new \RuntimeException('iblock id not found');
        }

        $existingProp = \CIBlockProperty::getList([],[
           'IBLOCK_ID' => $iblockId,
           'CODE' => 'FILE_ATTACHMENT'
        ])->Fetch();

        if($existingProp) {
            $ibp = new \CIBlockProperty();

            $result = $ibp->Add([
                'NAME' => 'Attachment File',
                'ACTIVE' => 'Y',
                'SORT' => 500,
                'CODE' => 'FILE_ATTACHMENT',
                'PROPERTY_TYPE' => 'F', // F = файл
                'IBLOCK_ID' => $iblockId,
            ]);

            if (!$result) {
                throw new \RuntimeException($ibp->LAST_ERROR);
            }

            echo "Property FILE_ATTACHMENT added to iblock.\n";
        } else {
            echo "Property FILE_ATTACHMENT already exists.\n";
        }

    }
}