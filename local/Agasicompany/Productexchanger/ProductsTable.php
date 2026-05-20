<?php
namespace Agasicompany\Productexchanger;

use Bitrix\Main\Entity;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;

class ProductsTable extends DataManager
{
    public static function getTableName()
    {
        return "agasicompany_produts";
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID',[
                'primary' => true,
                'autocomplete' => true,
            ]),

            new Entity\StringField('TITLE', [
                'required' => true,
            ]),

            new Entity\StringField('CODE', [
                'required' => true,
                'unique' => true,
            ]),

            new Entity\StringField('DESCRIPTION'),

            new Entity\IntegerField('PRICE',[
                'required' => true,
            ]),

            new Entity\StringField('AUTHOR',[
                'required' => false,
            ]),

            new Entity\IntegerField('CREATED_BY'),

            new Entity\IntegerField('UPDATED_BY'),

            new Entity\DatetimeField('CREATED_AT',[
                'default_value' => function() {
                    return new DateTime();
                }
            ]),

            new Entity\DatetimeField('UPDATED_AT', [
                'default_value' => function() {
                    return new DateTime();
                }
            ]),

            new Entity\ReferenceField(
                'CREATED_USER',
                UserTable::class,
                ['=this.CREATED_BY' => 'ref.ID']
            ),

            new Entity\ReferenceField(
                'UPDATED_USER',
                UserTable::class,
                ['=this.CREATED_BY' => 'ref.ID']
            )

        ];
    }
}
