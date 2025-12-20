<?php
namespace OtusApp\OtusDebug;

use Bitrix\Main\Diag\ExceptionHandlerFormatter;
use Bitrix\Main\Diag\FileExceptionHandlerLog;

class MyCustomLogger extends FileExceptionHandlerLog {

    /**
     * @param $message
     * @param $clear
     * @param $fileName
     * @param $timeVersion
     * @return void
     */
    public static function logOfMine($message, $clear = false, $fileName = 'log_custom', $timeVersion = true): void
    {
        $logsDir = $_SERVER['DOCUMENT_ROOT'] . '/local/logs/';

        // Создаём папку, если не существует
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true);
        }

        $logFile = $logsDir . $fileName;
        if ($timeVersion) {
            $logFile .= '_' . date('d.m.Y');
        }
        $logFile .= '.log';

        // Формируем сообщение
        $_message = date("d.m.Y H:i:s") . "\n";
        $_message .= print_r($message, true) . "\n";
        $_message .= "___\n";

        // Записываем
        if ($clear) {
            file_put_contents($logFile, "");
        } else {
            file_put_contents($logFile, $_message, FILE_APPEND);
        }
    }




}