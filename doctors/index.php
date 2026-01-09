<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/bitrix/header.php";
/**
 * @var $APPLICATION
 */
$APPLICATION->SetTitle("Доктора");

use Bitrix\Main\Loader;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;

Loader::includeModule("iblock");

define('IBLOCK_TYPE','content');

define('IBLOCK_PROCEDURS_CODE','API_PROCEDURS');
define('IBLOCK_DOCTORS_CODE','API_DOCTORS');

/**
 * Метод создает инфоблок если не существует
 *
 * @param $name
 * @param $code
 * @return int
 * @throws \Bitrix\Main\ArgumentException
 * @throws \Bitrix\Main\ObjectPropertyException
 * @throws \Bitrix\Main\SystemException
 */
function getOrCreateIblock($name, $code)
{
    $iblock = IblockTable::getList([
       'filter' => ['=CODE' => $code],
       'select' => ['ID'],
    ])->fetch();

    if($iblock) {
        return (int)$iblock['ID'];
    }

    $ib = new CIBlock();
    return (int)$ib->Add([
        'NAME' => $name,
        'CODE' => $code,
        'IBLOCK_TYPE_ID' => IBLOCK_TYPE,
        'SITE_ID' => ['s1'],
        'ACTIVE' => 'Y',
        'VERSION' => '1.0',
    ]);
}

/**
 * Метод создает свойство, если его нет в инфоблоке
 *
 * @param array $fields
 * @return void
 * @throws \Bitrix\Main\ArgumentException
 * @throws \Bitrix\Main\ObjectPropertyException
 * @throws \Bitrix\Main\SystemException
 */
function createPropertyIfNotExist(array $fields)
{
    $exists = PropertyTable::getList([
        'filter' => [
            '=CODE' => $fields['CODE'],
            '=IBLOCK_ID' => $fields['IBLOCK_ID'],
        ],
    ])->fetch();

    if($exists) {
        return;
    }

    $property = new CIBlockProperty();
    $property->Add($fields);
}

?>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/bitrix/footer.php"; ?>
