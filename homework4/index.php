<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
use Bitrix\Main\Loader;
use OtusApp\OtusDzfour\ProductsTable;
use OtusApp\OtusDzfour\MyOtusProductsTableCreation;

/**
 * @var $APPLICATION
 */

$APPLICATION->SetTitle("Домашнее задание №4");

Loader::includeModule('iblock');

// ✅ Создаём таблицу и тестовые данные, если ещё нет
MyOtusProductsTableCreation::migrateAndFillDemoData();

// ✅ Гарантируем, что инфоблоки готовы
ProductsTable::ensureIblocks();

// Выборка продуктов с данными из инфоблоков
$products = [];
$res = ProductsTable::getList([
    'select' => [
        'ID',
        'NAME',
        'PRICE',
        'CATEGORY_ID',
        'MANUFACTURER_ID',
        'CATEGORY_NAME' => 'CATEGORY.NAME',
        'MANUFACTURER_NAME' => 'MANUFACTURER.NAME',
    ],
    'order' => ['ID' => 'ASC'],
]);

while ($product = $res->fetch()) {
    $products[] = $product;
}

// Вывод
?>
    <h2>Список продуктов</h2>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Категория</th>
            <th>Производитель</th>
            <th>Цена</th>
        </tr>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><?= $p['ID'] ?></td>
                <td><?= htmlspecialchars($p['NAME']) ?></td>
                <td><?= htmlspecialchars($p['CATEGORY_NAME']) ?></td>
                <td><?= htmlspecialchars($p['MANUFACTURER_NAME']) ?></td>
                <td><?= number_format($p['PRICE'], 0, '', ' ') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';