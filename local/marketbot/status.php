<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

try {
    require_once __DIR__ . '/lib/bootstrap.php';
    AppConfig::load();

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

    echo "portal: " . $restTenant['DOMAIN'] . "\n";
    echo "bot_id in db: " . $restTenant['BOT_ID'] . "\n\n";

    $response = (new BitrixRest())->callRaw($restTenant, 'imbot.bot.list', [], $repository);
    if (!empty($response['error'])) {
        throw new RuntimeException((string)($response['error_description'] ?? $response['error']));
    }

    echo "imbot.bot.list:\n";
    $bots = $response['result'] ?? [];
    if (!is_array($bots) || $bots === []) {
        echo "(empty)\n";
    } else {
        foreach ($bots as $bot) {
            if (!is_array($bot)) {
                continue;
            }
            echo '---' . "\n";
            foreach (['ID', 'BOT_ID', 'CODE', 'APP_ID', 'TYPE', 'OPENLINE', 'METHOD_MESSAGE_ADD'] as $key) {
                if (array_key_exists($key, $bot)) {
                    echo $key . ': ' . (is_scalar($bot[$key]) ? $bot[$key] : json_encode($bot[$key])) . "\n";
                }
            }
        }
    }

    if (!empty($_GET['reset'])) {
        $botId = (int)($tenant['BOT_ID'] ?? 0);
        if ($botId > 0) {
            $unregister = (new BitrixRest())->callRaw($restTenant, 'imbot.unregister', [
                'BOT_ID' => $botId,
                'CLIENT_ID' => AppConfig::load()['client_id'],
            ], $repository);
            echo "\nimbot.unregister: " . json_encode($unregister, JSON_UNESCAPED_UNICODE) . "\n";
        }

        $repository->clearBotId((string)$tenant['MEMBER_ID']);

        $restTenant['BOT_ID'] = 0;
        $newId = (new BotInstaller())->install($restTenant, $repository);
        echo "new bot_id: " . $newId . "\n";
    } else {
        echo "\nreset bot: status.php?reset=1\n";
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo 'error: ' . $e->getMessage();
}
