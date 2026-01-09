<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/bitrix/header.php";
/**
 * @var $APPLICATION
 */
$APPLICATION->SetTitle("Доктора");

use Bitrix\Main\Loader;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;

Loader::includeModule("iblock");

define('IBLOCK_TYPE','structure');
define('IBLOCK_PROCEDURES_CODE','procedures');
define('IBLOCK_DOCTORS_CODE','doctors');

/**
 * Создает инфоблок, если не существует
 */
function getOrCreateIblock($name, $code)
{
    $iblock = IblockTable::getList([
        'filter' => ['=CODE' => $code],
        'select' => ['ID']
    ])->fetch();

    if ($iblock) return (int)$iblock['ID'];

    $ib = new CIBlock();
    $id = $ib->Add([
        'NAME' => $name,
        'CODE' => $code,
        'IBLOCK_TYPE_ID' => IBLOCK_TYPE,
        'SITE_ID' => ['s1'],
        'ACTIVE' => 'Y',
        'VERSION' => 2
    ]);

    if (!$id) throw new \RuntimeException($ib->LAST_ERROR);
    return (int)$id;
}

/**
 * Создает свойство инфоблока, если его нет
 */
function createPropertyIfNotExists(array $fields)
{
    $exists = PropertyTable::getList([
        'filter' => [
            '=CODE' => $fields['CODE'],
            '=IBLOCK_ID' => $fields['IBLOCK_ID'],
        ]
    ])->fetch();

    if ($exists) return;

    $property = new CIBlockProperty();
    $property->Add($fields);
}

/**
 * Заполняет демо данными, если их нет
 */
function fillDemoData($iblockId, array $elements)
{
    $exists = CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId], false, ['nTopCount' => 1])->Fetch();
    if ($exists) return;

    $el = new CIBlockElement();
    foreach ($elements as $element) {
        $result = $el->Add([
            'IBLOCK_ID' => $iblockId,
            'NAME' => $element['NAME'],
            'ACTIVE' => 'Y',
            'PROPERTY_VALUES' => $element['PROPS'] ?? []
        ]);
        if (!$result) throw new \RuntimeException($el->LAST_ERROR);
    }
}

// --- Создаем инфоблоки ---
$proceduresIblockId = getOrCreateIblock('Процедуры', IBLOCK_PROCEDURES_CODE);
$doctorsIblockId = getOrCreateIblock('Доктора', IBLOCK_DOCTORS_CODE);

// --- Свойства инфоблока Доктора ---
createPropertyIfNotExists([
    'IBLOCK_ID' => $doctorsIblockId,
    'NAME' => 'Процедуры',
    'CODE' => 'PROCEDURES',
    'PROPERTY_TYPE' => 'E',
    'LINK_IBLOCK_ID' => $proceduresIblockId,
    'MULTIPLE' => 'Y',
    'ACTIVE' => 'Y'
]);

createPropertyIfNotExists([
    'IBLOCK_ID' => $doctorsIblockId,
    'NAME' => 'Кабинет',
    'CODE' => 'CABINET',
    'PROPERTY_TYPE' => 'L',
    'LIST_TYPE' => 'L',
    'ACTIVE' => 'Y',
    'VALUES' => [
        ['VALUE' => '№1'], ['VALUE' => '№2'], ['VALUE' => '№3'],
        ['VALUE' => '№4'], ['VALUE' => '№5'], ['VALUE' => '№6'],
        ['VALUE' => '№7'], ['VALUE' => '№8'], ['VALUE' => '№9'],
    ]
]);

// --- Демо процедуры ---
fillDemoData($proceduresIblockId, [
    ['NAME' => 'УЗИ'], ['NAME' => 'ЭКГ'], ['NAME' => 'МРТ'], ['NAME' => 'КТ'],
    ['NAME' => 'Рентгенография'], ['NAME' => 'Эндоскопия'], ['NAME' => 'Колоноскопия'],
    ['NAME' => 'Гастроскопия'], ['NAME' => 'Лабораторные анализы крови'], ['NAME' => 'Функциональная диагностика'],
]);

// --- Получаем ID процедур ---
$procedures = [];
$rsProc = CIBlockElement::GetList([], ['IBLOCK_ID' => $proceduresIblockId], false, false, ['ID']);
while ($p = $rsProc->Fetch()) {
    $procedures[] = $p['ID'];
}

// --- Получаем ID значений кабинета ---
$cabinetProp = CIBlockProperty::GetList([], ['IBLOCK_ID' => $doctorsIblockId, 'CODE' => 'CABINET'])->Fetch();
$cabinetValues = [];
$enum = CIBlockPropertyEnum::GetList([], ['PROPERTY_ID' => $cabinetProp['ID']]);
while ($val = $enum->Fetch()) $cabinetValues[] = $val['ID'];

// --- Демо доктора с рандомными кабинетами ---
$demoDoctors = [
    'Иванов Иван Иванович' => [$procedures[0], $procedures[1]],
    'Петров Петр Петрович' => [$procedures[2]],
    'Сидорова Анна Сергеевна' => [$procedures[0]],
    'Кузнецов Дмитрий Олегович' => [$procedures[3], $procedures[4]],
    'Морозова Елена Викторовна' => [$procedures[8]],
    'Алексеев Михаил Андреевич' => [$procedures[5], $procedures[6]],
    'Романова Ольга Николаевна' => [$procedures[7]],
    'Захаров Артем Валерьевич' => [$procedures[1], $procedures[9]],
    'Белова Наталья Игоревна' => [$procedures[0], $procedures[9]],
];

$demoElements = [];
foreach ($demoDoctors as $name => $procIds) {
    $randomCabinetId = $cabinetValues[array_rand($cabinetValues)]; // случайный кабинет
    $demoElements[] = [
        'NAME' => $name,
        'PROPS' => [
            'CABINET' => $randomCabinetId,
            'PROCEDURES' => $procIds
        ]
    ];
}
fillDemoData($doctorsIblockId, $demoElements);

$res = CIBlockElement::GetList(['NAME'=>'ASC'], ['IBLOCK_ID'=>$doctorsIblockId,'ACTIVE'=>'Y'], false, false, ['ID','NAME']);

echo '<h2>Список докторов</h2>';

while ($doctor = $res->GetNextElement()) {
    $fields = $doctor->GetFields();
    $props = $doctor->GetProperties();

    echo '<div style="margin-bottom:15px;">';
    echo '<strong>' . htmlspecialcharsbx($fields['NAME']) . '</strong><br>';
    echo 'Кабинет: ' . htmlspecialcharsbx($props['CABINET']['VALUE']) . '<br>';

    if (!empty($props['PROCEDURES']['VALUE'])) {
        $procNames = [];
        $rsP = CIBlockElement::GetList([], ['ID' => $props['PROCEDURES']['VALUE']], false, false, ['NAME']);
        while ($p = $rsP->Fetch()) $procNames[] = $p['NAME'];
        echo 'Процедуры: ' . implode(', ', $procNames);
    }

    echo '</div>';
}

?>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/bitrix/footer.php"; ?>