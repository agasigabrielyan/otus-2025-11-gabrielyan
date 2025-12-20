<?php
namespace OtusApp\OtusException;

use Bitrix\Main\Diag\ExceptionHandlerFormatter;
use Bitrix\Main\Diag\FileExceptionHandlerLog;

class MyCustomFileExceptionHandler extends FileExceptionHandlerLog
{
    protected $level;

    /**
     * @param $exception
     * @param $logType
     * @return void
     */
    public function write($exception, $logType): void
    {
        // Проверяем, есть ли папка /local/logs, и создаем если нет
        $logDir = $_SERVER['DOCUMENT_ROOT'] . '/local/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $text = ExceptionHandlerFormatter::format($exception, false, $this->level);

        $context = [
            'type' => static::logTypeToString($logType),
        ];

        $logLevel = static::logTypeToLevel($logType);

        $message = "OTUS - {date} - Host: {host} - {type} - {$text}\n";

        $this->logger->log($logLevel, $message, $context);
    }
}