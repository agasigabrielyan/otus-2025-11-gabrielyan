<?php
$dir = $_SERVER['DOCUMENT_ROOT'] . '/webhook/cloud';
$logFile = $dir . '/deal_status.log';
$mapFile = $dir . '/status_map.json';

if (!is_dir($dir)) {
    mkdir($dir, 0775, true);
}

$raw = file_get_contents('php://input') ?: '';
parse_str($raw, $post);

$event  = (string)($post['event'] ?? '');
$dealId = (int)($post['data']['FIELDS']['ID'] ?? 0);

if ($event === 'ONCRMDEALUPDATE' && $dealId > 0) {
    // статус сделки в облаке (берем по входящему вебхуку облака)
    $cloudWebhook = 'https://b24-1jxzsv.bitrix24.ru/rest/1/PASTE_WEBHOOK_CODE/';
    $url = rtrim($cloudWebhook, '/') . '/crm.deal.get.json?id=' . $dealId;

    $json = @file_get_contents($url);
    $resp = json_decode((string)$json, true);

    $stageId = (string)($resp['result']['STAGE_ID'] ?? '');
    $title   = (string)($resp['result']['TITLE'] ?? '');

    if ($stageId !== '') {
        $map = [];
        if (is_file($mapFile)) {
            $map = json_decode((string)file_get_contents($mapFile), true) ?: [];
        }

        $map[$dealId] = [
            'deal_id' => $dealId,
            'title' => $title,
            'stage_id' => $stageId,
            'updated_at' => date('c'),
        ];

        file_put_contents(
            $mapFile,
            json_encode($map, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            LOCK_EX
        );
    }
}

$line = date('Y-m-d H:i:s')
    . " | event={$event} | deal_id={$dealId} | raw={$raw}" . PHP_EOL;
file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);

http_response_code(200);
echo 'OK';
echo 'OK';