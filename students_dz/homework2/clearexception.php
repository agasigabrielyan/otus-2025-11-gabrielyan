<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
use OtusApp\OtusDebug;
OtusDebug\MyCustomLogger::logOfMine("", true, "exceptions", false);
?>
<div>
    Файл логов exceptins очищен ( <a target="_blank" href="/local/logs/exceptions.log">Убедиться</a> )
</div>

