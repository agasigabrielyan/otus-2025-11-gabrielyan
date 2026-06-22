<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

try {
    require_once __DIR__ . '/lib/bootstrap.php';
    $config = AppConfig::load();

    $repository = new TenantRepository();
    $tenant = $repository->loadSingle();
    if ($tenant === null) {
        echo "tenant: not found\n";
        exit;
    }

    $restTenant = [
        'MEMBER_ID' => (string)$tenant['MEMBER_ID'],
        'DOMAIN' => (string)$tenant['DOMAIN'],
        'AUTH_ID' => (string)$tenant['AUTH_ID'],
        'REFRESH_ID' => (string)$tenant['REFRESH_ID'],
        'BOT_ID' => (int)($tenant['BOT_ID'] ?? 0),
    ];

    $baseUrl = dirname($config['handler_url']);
    $pingUrl = $baseUrl . '/ping.php';
    $handlerUrl = $config['handler_url'];

    echo "portal: " . $restTenant['DOMAIN'] . "\n";
    echo "bot_id in db: " . $restTenant['BOT_ID'] . "\n\n";

    if (!empty($_GET['ping'])) {
        (new BotInstaller())->updateHandler($restTenant, $repository, $pingUrl);
        echo "handler switched to ping.php\n";
        echo $pingUrl . "\n\n";
    }

    if (!empty($_GET['restore'])) {
        (new BotInstaller())->updateHandler($restTenant, $repository, $handlerUrl);
        echo "handler restored to handler.php\n";
        echo $handlerUrl . "\n\n";
    }

    $response = (new BitrixRest())->callRaw($restTenant, 'imbot.bot.list', [], $repository);
    if (!empty($response['error'])) {
        throw new RuntimeException((string)($response['error_description'] ?? $response['error']));
    }

    echo "our bot:\n";
    foreach (($response['result'] ?? []) as $bot) {
        if (!is_array($bot)) {
            continue;
        }
        if ((int)($bot['ID'] ?? 0) !== (int)$restTenant['BOT_ID']) {
            continue;
        }
        echo 'ID: ' . ($bot['ID'] ?? '') . "\n";
        echo 'CODE: ' . ($bot['CODE'] ?? '') . "\n";
        echo 'OPENLINE: ' . ($bot['OPENLINE'] ?? '') . "\n";
    }

    echo "\nevents:\n";
    foreach ((new EventBinder())->listEvents($restTenant, $repository) as $event) {
        if (!is_array($event)) {
            continue;
        }
        if (($event['event'] ?? '') === 'ONIMBOTMESSAGEADD') {
            echo ($event['event'] ?? '') . ' -> ' . ($event['handler'] ?? '') . "\n";
        }
    }

    echo "\ncommands:\n";
    echo "test ping handler: status.php?ping=1\n";
    echo "restore handler: status.php?restore=1\n";
    echo "reset bot: status.php?reset=1\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo 'error: ' . $e->getMessage();
}
