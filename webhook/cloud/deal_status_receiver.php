<?php

$dir = $_SERVER['DOCUMENT_ROOT'] . '/webhook/cloud';
$logFile = $dir . '/deal_status.log';
$mapFile = $dir . '/status_map.json';

if (!is_dir($dir)) {
    mkdir($dir, 0775, true);
}

function logLine(string $message, string $logFile): void
{
    file_put_contents(
        $logFile,
        date('Y-m-d H:i:s') . ' | ' . $message . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

$raw = file_get_contents('php://input') ?: '';
parse_str($raw, $post);

$event = (string)($post['event'] ?? '');
$dealId = (int)($post['data']['FIELDS']['ID'] ?? 0);

logLine("incoming event={$event}, deal_id={$dealId}, raw={$raw}", $logFile);

if ($event === 'ONCRMDEALUPDATE' && $dealId > 0) {
    // Вставь реальный код входящего вебхука облака вместо PASTE_WEBHOOK_CODE
    $cloudWebhook = 'https://b24-1jxzsv.bitrix24.ru/rest/1/9f6ox41myp4u28eu/';
    if (strpos($cloudWebhook, '9f6ox41myp4u28eu') !== false) {
        logLine('skip: cloud webhook code is not configured', $logFile);
    } else {
        $url = rtrim($cloudWebhook, '/') . '/crm.deal.get.json?id=' . $dealId;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
        ]);
        $json = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($json === false || $json === '') {
            logLine('crm.deal.get failed: ' . $curlError, $logFile);
        } else {
            $resp = json_decode((string)$json, true);
            $stageId = (string)($resp['result']['STAGE_ID'] ?? '');
            $title = (string)($resp['result']['TITLE'] ?? '');
            logLine("crm.deal.get stage={$stageId}, title={$title}", $logFile);

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

            $saved = file_put_contents(
                $mapFile,
                json_encode($map, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                LOCK_EX
            );

            logLine('map write result=' . ($saved !== false ? 'true' : 'false'), $logFile);
        }
    }
}

http_response_code(200);
echo 'OK';