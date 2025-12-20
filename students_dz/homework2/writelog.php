<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
/**
 * @var $APPLICATION
 */
$APPLICATION->SetTitle("Добавление в лог");
?>
<?php
use OtusApp\OtusDebug;

$currentPage = __FILE__;
$message = "Была открыта страница - " . $currentPage . date('Y-m-d H:i:s') . "\n";
OtusDebug\MyCustomLogger::logOfMine($message, false, "log_custom", false);
?>
    <ul class="list-group">
        <li class="list-group-item">
            <a href="/local/logs/log_custom.log">Файл лога</a>, в лог добавленно 'Открыта страница writelog.php'
        </li>
    </ul>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>