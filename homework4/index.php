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
<p>
    <div>
        <div class="mb-3">Репозиторий: <a target="_blank" href="https://github.com/agasigabrielyan/otus-2025-11-gabrielyan">https://github.com/agasigabrielyan/otus-2025-11-gabrielyan</a></div>
        <div class="mb-3">ORM класс таблицы products :
            <a target="_blank" href="/bitrix/admin/fileman_file_edit.php?path=%2Flocal%2FOtusApp%2FOtusDzfour%2FProductsTable.php&full_src=Y&site=s1&lang=ru&&filter=Y&set_filter=Y">
                /bitrix/admin/fileman_file_edit.php?path=%2Flocal%2FOtusApp%2FOtusDzfour%2FProductsTable.php&full_src=Y&site=s1&lang=ru&&filter=Y&set_filter=Y
            </a>
        </div>
        <div class="mb-3">Инфоблок категории :
            <a target="_blank" href="/bitrix/admin/iblock_list_admin.php?IBLOCK_ID=20&type=lists&lang=ru&find_section_section=0&SECTION_ID=0&apply_filter=Y">
                /bitrix/admin/iblock_list_admin.php?IBLOCK_ID=20&type=lists&lang=ru&find_section_section=0&SECTION_ID=0&apply_filter=Y
            </a>
        </div>
        <div class="mb-3">Инфоблок производители :
            <a target="_blank" href="/bitrix/admin/iblock_list_admin.php?IBLOCK_ID=21&type=lists&lang=ru&find_section_section=0&SECTION_ID=0&apply_filter=Y">
                /bitrix/admin/iblock_list_admin.php?IBLOCK_ID=21&type=lists&lang=ru&find_section_section=0&SECTION_ID=0&apply_filter=Y
            </a>
        </div>
    </div>
</p>
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