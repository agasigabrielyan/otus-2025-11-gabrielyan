<?php

declare(strict_types=1);

@file_put_contents(__DIR__ . '/data/handler.log', date('Y-m-d H:i:s') . " ping " . ($_SERVER['REQUEST_METHOD'] ?? 'GET') . "\n", FILE_APPEND);

http_response_code(200);
header('Content-Type: text/plain; charset=utf-8');
echo 'ping ok';
