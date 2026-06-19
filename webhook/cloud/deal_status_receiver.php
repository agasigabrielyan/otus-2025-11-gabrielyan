<?php
$logFile = $_SERVER['DOCUMENT_ROOT'] . '/webhook/cloud/deal_status.log';
$raw = file_get_contents('php://input');

file_put_contents(
    $logFile,
    date('Y-m-d H:i:s' . "\n" . $raw . "\n----\n"),
    FILE_APPEND | LOCK_EX
);

http_response_code(200);
echo "OK";