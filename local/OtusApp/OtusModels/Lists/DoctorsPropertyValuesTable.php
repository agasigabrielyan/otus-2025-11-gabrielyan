<?php

namespace OtusApp\OtusModels\Lists;

use Bitrix\Main\Entity\ReferenceField;
use OtusApp\OtusModels\AbstractIblockPropertyValuesTable;

class DoctorsPropertyValuesTable extends AbstractIblockPropertyValuesTable
{
    public const IBLOCK_ID = 19;

    public static function getMap(): array
    {
        $map = [
            'PROCEDURES' => new ReferenceField(
                'PROCEDURES',
                DoctorProcedurePropertyValuesTable::class,
                ['=this.PROCEDURES_ID' => 'ref.IBLOCK_ELEMENT_ID']
            )
        ];

        return parent::getMap() + $map;
    }

}