<?php

namespace Otus\Marketdemo\ORM;

use Bitrix\Main\ORM\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Type\DateTime;

class DemoItemTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'otus_marketdemo_demo_item';
    }

    public static function getMap(): array
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new StringField('TITLE', [
                'required' => true,
                'validation' => static function () {
                    return [new LengthValidator(null, 255)];
                },
            ]),
            new TextField('BODY', [
                'required' => true,
            ]),
            new StringField('STATUS', [
                'required' => true,
                'default_value' => 'active',
                'validation' => static function () {
                    return [new LengthValidator(null, 32)];
                },
            ]),
            new DateField('DATE_PUBLISH', [
                'required' => true,
            ]),
            new DatetimeField('DATE_CREATE', [
                'required' => true,
                'default_value' => static fn () => new DateTime(),
            ]),
        ];
    }
}
