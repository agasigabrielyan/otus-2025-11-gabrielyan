<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("taskmanagerviewer");
?><?$APPLICATION->IncludeComponent(
	"Otus:taskmanagerviewer",
	"",
Array()
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>