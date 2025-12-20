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
        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/local/logs/' . $fileName;
        if($timeVersion) {
            $logFile .= '_' . date('d.m.Y');
        }
        $logFile .= '.log';

        $_message = date("d.m.Y H:i:s");
        $_message .= "\n";
        $_message .= print_r($message, true);
        $_message .= "\n";
        $_message .= "___";
        $_message .= "\n";

        if($clear) {
            file_put_contents($logFile, "");
        } else {
            file_put_contents($logFile, $_message, FILE_APPEND);
        }
    }




}