<?php

declare(strict_types=1);

namespace Otus\OriginalContactsData;

final class RestLog
{
    public static function write(string $action, array $context = []): void
    {
        $line = date('Y-m-d H:i:s') . ' [' . $action . '] ' . json_encode(
            $context,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) . PHP_EOL;

        @file_put_contents(__DIR__ . '/../data/rest.log', $line, FILE_APPEND);
    }
}
