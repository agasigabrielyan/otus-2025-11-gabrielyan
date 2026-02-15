<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
/**
 * @var $APPLICATION
 */
$APPLICATION->SetTitle("ДЗ к урок №12 Компонент списка таблицы БД ");
?>
<?$APPLICATION->IncludeComponent(
	"Otus:OtusCurrencies", 
	".default", 
	array(
		"CURRENCY" => "BYN",
		"COMPONENT_TEMPLATE" => ".default"
	),
	false
);?><?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>