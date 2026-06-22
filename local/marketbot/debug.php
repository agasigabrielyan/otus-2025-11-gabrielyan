<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

try {
    require_once __DIR__ . '/lib/bootstrap.php';
    AppConfig::load();

    $repository = new TenantRepository();
    $repository->ensureTable();

    echo "config.php: ok\n";
    echo "table: otus_marketbot_tenant\n";
    echo "rows: " . $repository->countAll() . "\n\n";

    $tenant = $repository->loadSingle();
    if ($tenant === null) {
        echo "tenant: not found\n";
        exit;
    }

    echo 'tenant: ' . ($tenant['MEMBER_ID'] ?? '') . ' @ ' . ($tenant['DOMAIN'] ?? '') . ' bot=' . ($tenant['BOT_ID'] ?? '') . "\n\n";

    $restTenant = [
        'MEMBER_ID' => (string)$tenant['MEMBER_ID'],
        'DOMAIN' => (string)$tenant['DOMAIN'],
        'AUTH_ID' => (string)$tenant['AUTH_ID'],
        'REFRESH_ID' => (string)$tenant['REFRESH_ID'],
        'BOT_ID' => (int)($tenant['BOT_ID'] ?? 0),
    ];

    $binder = new EventBinder();
  if (!empty($_GET['rebind'])) {
        (new BotInstaller())->updateHandler($restTenant, $repository);
        $binder->bindBotEvents($restTenant, $repository);
        echo "rebind: ok\n\n";
    }

    echo "events:\n";
    $events = $binder->listEvents($restTenant, $repository);
    if ($events === []) {
        echo "(empty)\n";
    } else {
        foreach ($events as $event) {
            if (!is_array($event)) {
                continue;
            }
            echo ($event['event'] ?? '?') . ' -> ' . ($event['handler'] ?? '?') . "\n";
        }
    }

    echo "\nhandler.log:\n";
    $logPath = __DIR__ . '/data/handler.log';
    if (is_file($logPath)) {
        $lines = file($logPath, FILE_IGNORE_NEW_LINES);
        $tail = array_slice($lines ?: [], -10);
        echo $tail === [] ? "(empty)\n" : implode("\n", $tail) . "\n";
    } else {
        echo "(no log yet)\n";
    }

    echo "\nrebind url: " . AppConfig::load()['handler_url'] . "\n";
    echo "run rebind: debug.php?rebind=1\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo 'error: ' . $e->getMessage();
}
