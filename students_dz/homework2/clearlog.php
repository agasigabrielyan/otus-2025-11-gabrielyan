<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
use OtusApp\OtusDebug;

OtusDebug\MyCustomLogger::logOfMine("", true, "log_custom", false);

?>
<div>
    Файл логов очищен ( <a target="_blank" href="/local/logs/log_custom.log">Убедиться</a> )
</div>

