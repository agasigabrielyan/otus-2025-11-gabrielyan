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
    echo "rows: " . $repository->countAll() . "\n";

    $db = Database::connection();
    $result = $db->query('SELECT MEMBER_ID, DOMAIN, BOT_ID, UPDATED_AT FROM otus_marketbot_tenant LIMIT 5');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo 'tenant: ' . ($row['MEMBER_ID'] ?? '') . ' @ ' . ($row['DOMAIN'] ?? '') . ' bot=' . ($row['BOT_ID'] ?? '') . ' (' . ($row['UPDATED_AT'] ?? '') . ")\n";
        }
    }

    $logPath = dirname(__DIR__) . '/data/handler.log';
    echo "\nhandler.log:\n";
    if (is_file($logPath)) {
        $lines = file($logPath, FILE_IGNORE_NEW_LINES);
        $tail = array_slice($lines ?: [], -10);
        echo $tail === [] ? "(empty)\n" : implode("\n", $tail) . "\n";
    } else {
        echo "(no log yet)\n";
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo 'error: ' . $e->getMessage();
}
