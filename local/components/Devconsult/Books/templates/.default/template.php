<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>

<script src="<?= $templateFolder ?>/script.js"></script>

<div class="book-add-form" style="max-width: 420px; margin-bottom: 16px;">
    <div style="margin-bottom: 8px;">
        <input id="book-title" type="text" placeholder="Название" style="width: 100%; padding: 6px;">
    </div>
    <div style="margin-bottom: 8px;">
        <input id="book-author" type="text" placeholder="Автор" style="width: 100%; padding: 6px;">
    </div>
    <div style="margin-bottom: 8px;">
        <input id="book-price" type="number" min="0" value="0" placeholder="Цена" style="width: 100%; padding: 6px;">
    </div>
    <button type="button" onclick="BX.Otus.BookGrid.addBook()">Добавить книгу</button>
</div>

<?php
    $APPLICATION->IncludeComponent(
        'bitrix:main.ui.grid',
        '',
        [
            'GRID_ID' => $arResult['GRID_ID'],
            'COLUMNS' => $arResult['GRID_HEADERS'],
            'ROWS' => $arResult['GRID_ROWS'],
            'SHOW_ROW_CHECKBOXES' => false,
            'ALLOW_COLUMNS_SORT' => true,
            'ALLOW_SORT' => true,
            'ALLOW_COLUMNS_RESIZE' => true,
            'ALLOW_HORIZONTAL_SCROLL' => true,
            'ALLOW_PIN_HEADER' => true,
            'SHOW_CHECK_ALL_CHECKBOXES' => false,
            'SHOW_GRID_SETTINGS_MENU' => true,
            'SHOW_NAVIGATION_PANEL' => false,
            'SHOW_PAGINATION' => false,
            'SHOW_TOTAL_COUNTER' => true,
            'SHOW_SELECTED_COUNTER' => false,
            'TOTAL_ROWS_COUNT' => count($arResult['GRID_ROWS']),
            'SHOW_PAGESIZE' => false,
            'SHOW_ACTION_PANEL' => false,
        ],
        $component
    );
?>

<script>
    BX.Otus.BookGrid.init({
        signedParams: `<?= $this->__component->getSignedParameters(); ?>`
    });
</script>
