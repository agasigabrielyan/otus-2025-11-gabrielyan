<?php

declare(strict_types=1);

/**
 * Outbound REST event handler (Holin PDF slide 29).
 * Bind via event.bind: handler = https://otusgabrielyan.local/local/otus_rest_event/
 */
@file_put_contents(
    __DIR__ . '/otusRest.log',
    date('Y-m-d H:i:s') . PHP_EOL . print_r($_REQUEST, true) . PHP_EOL . str_repeat('-', 40) . PHP_EOL,
    FILE_APPEND
);

header('Content-Type: text/plain; charset=utf-8');
echo 'event ok';
