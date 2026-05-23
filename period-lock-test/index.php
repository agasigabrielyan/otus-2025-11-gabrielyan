<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
/**
 * @var $APPLICATION
 */
$APPLICATION->SetTitle("Моя тестовая страница");

use Bitrix\Main\UI\Extension;
Extension::load("mycompany.periodlock.app");
?>
<div id="periodlock-app"></div>
<script>
    const app = new BX.Mycompany.Periodlock.App('#periodlock-app',{
        arResult: {message: 'SOME ARRESULT'},
        arParams: {
            entityCode: 'milestones'
        }
    });
    app.start();
</script>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
