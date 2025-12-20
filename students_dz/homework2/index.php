<?php use Bitrix\Main\Page\Asset;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?php
/**
 * @var $APPLICATION
 */
$APPLICATION->SetTitle("ДЗ #2: Отладка и логирование");

Asset::getInstance()->addCss('//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');


?>
<h1 class="mb-3"><?php $APPLICATION->ShowTitle() ?></h1>

<h4 class="mb-3">Часть 1 - Logger</h4>
<ul class="list-group">
    <li class="list-group-item">
        <a target="_blank" href="/local/logs/log_custom.log">Файл лога из п1 ДЗ</a>
    </li>
    <li class="list-group-item">
        <a target="_blank" href="writelog.php">Добавление в лог из п1 ДЗ</a>
    </li>
    <li class="list-group-item">
        <a target="_blank" href="clearlog.php">Очистить лог из п1 ДЗ</a>
    </li>
    <li class="list-group-item">
        <a target="_blank" href="/bitrix/admin/fileman_file_edit.php?path=%2Flocal%2FOtusApp%2FOtusDebug%2FMyCustomLogger.php&full_src=Y&site=s1&lang=ru&&filter=Y&set_filter=Y">Файл с классом кастомного логгера</a>
    </li>
</ul>


<h4 class="mb-3 mt-5">Часть 2 - Exception</h4>
<ul class="list-group">
    <li class="list-group-item">
        <a target="_blank" href="/local/logs/exceptions.log">Файл лога из п2 ДЗ</a>
    </li>
    <li class="list-group-item">
        <a target="_blank" href="writeexception.php">Добавление в лог из п2 ДЗ</a>
    </li>
    <li class="list-group-item">
        <a target="_blank" href="clearexception.php">Очистить лог из п2 ДЗ</a>
    </li>
    <li class="list-group-item">
        <a target="_blank" href="/bitrix/admin/fileman_file_edit.php?path=%2Flocal%2FOtusApp%2FOtusException%2FMyCustomFileExceptionHandler.php&full_src=Y&site=s1&lang=ru&&filter=Y&set_filter=Y">Файл с классом системного исключений</a>
    </li>
</ul>


<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
