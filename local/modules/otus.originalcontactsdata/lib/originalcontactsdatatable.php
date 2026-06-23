<?php

namespace Otus\OriginalContactsData;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\Type\DateTime;

/**
 * ORM entity for custom REST CRUD (Holin webinar).
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CONTACT_ID int optional — link to CRM contact
 * <li> ORIGINAL_DATA text mandatory — stored payload
 * <li> DATE_CREATE datetime mandatory
 * </ul>
 */
class OriginalContactsDataTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'b_otus_original_contacts_data';
    }

    public static function getMap(): array
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary(true)
                ->configureAutocomplete(true),
            (new IntegerField('CONTACT_ID'))
                ->configureNullable(true),
            (new TextField('ORIGINAL_DATA'))
                ->configureRequired(true),
            (new StringField('SOURCE'))
                ->configureSize(255)
                ->configureNullable(true),
            (new DatetimeField('DATE_CREATE'))
                ->configureRequired(true)
                ->configureDefaultValue(static fn () => new DateTime()),
        ];
    }
}
