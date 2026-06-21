<?php

declare(strict_types=1);

final class InstallLog
{
    public static function write(string $message, array $context = []): void
    {
        $dir = dirname(__DIR__) . '/data';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $line = date('c') . ' ' . $message;
        if ($context !== []) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        file_put_contents($dir . '/install.log', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
