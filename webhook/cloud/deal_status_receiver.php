<?php
$logFile = $_SERVER['DOCUMENT_ROOT'] . '/webhook/cloud/deal_status.log';

$raw = file_get_contents('php://input') ?: '';
parse_str($raw, $post); // распарсили x-www-form-urlencoded

$event  = $post['event'] ?? '';
$dealId = $post['data']['FIELDS']['ID'] ?? '';

$line = date('Y-m-d H:i:s')
    . " | event={$event}"
    . " | deal_id={$dealId}"
    . " | raw=" . $raw
    . PHP_EOL;

file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);

http_response_code(200);
echo 'OK';