<?php
/**
 * @var $APPLICATION
 */
use Bitrix\Main\UI\Extension;

if( !defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true ) {
    die();
}

Extension::load('ui.buttons');

global $APPLICATION;

ob_start();
?>
<div class="pagetitle-container" style="border: 15px solid purple;">
    <a id="otus-deffered-button" href="#" class="ui-btn ui-btn-success ui-btn-sm">Это моя первая Отус кнопка</a>
</div>
<?php
$customHtml = ob_get_clean();

$APPLICATION->AddViewContent("inside_pagetitle", $customHtml, 2000);

\Bitrix\Main\Page\Asset::getInstance()->addString('<script>
    BX.ready(function () {
        var btn = document.getElementById("otus-deffered-button");
        if (btn) {
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                alert("Кнопочка пашет");
            });
        }
    });
</script>');
