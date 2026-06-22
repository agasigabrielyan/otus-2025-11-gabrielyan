<?php

declare(strict_types=1);

$line = date('Y-m-d H:i:s') . ' PING ' . ($_SERVER['REQUEST_METHOD'] ?? 'GET');
$line .= ' len=' . strlen((string)file_get_contents('php://input'));
$line .= ' ct=' . ($_SERVER['CONTENT_TYPE'] ?? '');
$line .= "\n";
@file_put_contents(__DIR__ . '/data/handler.log', $line, FILE_APPEND);

http_response_code(200);
header('Content-Type: text/plain; charset=utf-8');
echo 'ping ok';
