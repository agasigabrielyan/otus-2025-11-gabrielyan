<?php

declare(strict_types=1);

final class HandlerLog
{
    public static function write(string $message, array $context = []): void
    {
        $dir = dirname(__DIR__) . '/data';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $line = date('Y-m-d H:i:s') . ' ' . $message;
        if ($context !== []) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        $line .= PHP_EOL;

        @file_put_contents($dir . '/handler.log', $line, FILE_APPEND);
    }
}
