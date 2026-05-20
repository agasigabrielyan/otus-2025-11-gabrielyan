<?php
namespace Otus\TaskManager\ORM;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\TextField;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\UserTable;

class TaskCommentTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'otus_task_comments';
    }

    public static function getMap(): array
    {
        return [
            new IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new IntegerField('TASK_ID', ['required' => true]),
            new IntegerField('USER_ID', ['required' => true]),
            new TextField('COMMENT', ['required' => true]),
            new DatetimeField('CREATED_AT', ['default_value' => new \Bitrix\Main\Type\DateTime()]),

            // --- Reference к инфоблоку задач ---
            new ReferenceField(
                'TASK',
                ElementTable::class,
                ['=this.TASK_ID' => 'ref.ID'],
                ['join_type' => 'INNER']
            ),

            // --- Reference к пользователю ---
            new ReferenceField(
                'USER',
                UserTable::class,
                ['=this.USER_ID' => 'ref.ID'],
                ['join_type' => 'INNER']
            ),
        ];
    }
}