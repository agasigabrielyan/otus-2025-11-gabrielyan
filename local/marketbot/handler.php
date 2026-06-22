<?php

declare(strict_types=1);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/lib/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $repository = new TenantRepository();
    $repository->ensureTable();

    $input = TenantAuth::getInput();
    HandlerLog::write('handler called', [
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
        'event' => $input['event'] ?? null,
    ]);

    $auth = resolveEventAuth($input);
    if (TenantAuth::canSave($auth)) {
        $repository->save($auth['member_id'], $auth);
    }

    $memberId = resolveMemberId($input, $auth);
    if ($memberId === '') {
        HandlerLog::write('skip', ['reason' => 'no member_id']);
        http_response_code(200);
        echo 'skip: no member_id';
        exit;
    }

    $tenant = $repository->load($memberId);
    if ($tenant === null) {
        HandlerLog::write('skip', ['reason' => 'tenant not found', 'member_id' => $memberId]);
        http_response_code(200);
        echo 'skip: tenant not found';
        exit;
    }

    $dialogId = resolveDialogId($input);
    if ($dialogId === '') {
        HandlerLog::write('skip', ['reason' => 'no dialog_id']);
        http_response_code(200);
        echo 'skip: no dialog_id';
        exit;
    }

    $text = trim(resolveMessageText($input));
    $botId = resolveBotId($input, $tenant);
    if ($botId <= 0) {
        HandlerLog::write('skip', ['reason' => 'no bot_id']);
        http_response_code(200);
        echo 'skip: bot not registered';
        exit;
    }

    $restTenant = [
        'MEMBER_ID' => (string)$tenant['MEMBER_ID'],
        'DOMAIN' => (string)($auth['domain'] ?: $tenant['DOMAIN']),
        'AUTH_ID' => (string)($auth['auth_id'] ?: $tenant['AUTH_ID']),
        'REFRESH_ID' => (string)($auth['refresh_id'] ?: $tenant['REFRESH_ID']),
        'BOT_ID' => $botId,
    ];

    $reply = buildReplyText($text, $restTenant, $repository);

    $response = (new BitrixRest())->callRaw($restTenant, 'imbot.message.add', [
        'BOT_ID' => $botId,
        'DIALOG_ID' => $dialogId,
        'MESSAGE' => $reply,
    ], $repository);

    if (!empty($response['error'])) {
        $description = (string)($response['error_description'] ?? $response['error']);
        HandlerLog::write('reply failed', ['error' => $description]);
        throw new RuntimeException($description);
    }

    HandlerLog::write('reply sent', [
        'dialog_id' => $dialogId,
        'text' => $text,
        'reply' => $reply,
    ]);

    http_response_code(200);
    echo 'ok';
} catch (Throwable $e) {
    HandlerLog::write('error', ['message' => $e->getMessage()]);
    http_response_code(500);
    echo 'error: ' . $e->getMessage();
}

function resolveEventAuth(array $input): array
{
    $auth = TenantAuth::fromRequest($input);
    if (TenantAuth::canSave($auth)) {
        return $auth;
    }

    $bots = $input['data']['BOT'] ?? [];
    if (!is_array($bots)) {
        return $auth;
    }

    foreach ($bots as $bot) {
        if (!is_array($bot) || !isset($bot['AUTH']) || !is_array($bot['AUTH'])) {
            continue;
        }

        $botAuth = TenantAuth::fromRequest(['auth' => $bot['AUTH']]);
        if (TenantAuth::canSave($botAuth)) {
            return $botAuth;
        }
    }

    return $auth;
}

function resolveMemberId(array $input, array $auth): string
{
    if (!empty($auth['member_id'])) {
        return (string)$auth['member_id'];
    }

    $value = readByPaths($input, [
        ['auth', 'member_id'],
        ['member_id'],
    ]);

    return is_scalar($value) ? trim((string)$value) : '';
}

function resolveBotId(array $input, array $tenant): int
{
    $fromEvent = readByPaths($input, [
        ['data', 'PARAMS', 'BOT_ID'],
        ['data', 'BOT_ID'],
    ]);

    if (is_numeric($fromEvent) && (int)$fromEvent > 0) {
        return (int)$fromEvent;
    }

    $bots = $input['data']['BOT'] ?? [];
    if (is_array($bots)) {
        foreach ($bots as $bot) {
            if (is_array($bot) && !empty($bot['BOT_ID'])) {
                return (int)$bot['BOT_ID'];
            }
        }
    }

    return (int)($tenant['BOT_ID'] ?? 0);
}

function resolveDialogId(array $input): string
{
    $value = readByPaths($input, [
        ['data', 'PARAMS', 'DIALOG_ID'],
        ['data', 'PARAMS', 'FROM_USER_ID'],
        ['data', 'PARAMS', 'USER_ID'],
        ['DIALOG_ID'],
    ]);

    if ($value === null || $value === '') {
        return '';
    }

    return trim((string)$value);
}

function resolveMessageText(array $input): string
{
    $value = readByPaths($input, [
        ['data', 'PARAMS', 'MESSAGE'],
        ['data', 'PARAMS', 'MESSAGE_ORIGINAL'],
        ['data', 'PARAMS', 'MESSAGE_TEXT'],
        ['MESSAGE'],
    ]);

    return is_scalar($value) ? (string)$value : '';
}

function buildReplyText(string $text, array $tenant, TenantRepository $repository): string
{
    $normalized = trim($text);
    $command = function_exists('mb_strtolower') ? mb_strtolower($normalized) : strtolower($normalized);

    if (in_array($command, ['/stats', '/stat', 'stats', 'статистика'], true)) {
        $count = (new BitrixRest())->getDealCount($tenant, $repository);
        return 'Сделок в CRM: ' . $count;
    }

    if ($command === '/help' || $command === 'help' || $command === 'помощь') {
        return "Команды:\n/stats - количество сделок\n/help - помощь";
    }

    if ($text === '') {
        return "Я на связи. Напишите /stats для статистики CRM.";
    }

    return "Вы написали: {$text}\nПодсказка: /stats";
}

function readByPaths(array $data, array $paths): mixed
{
    foreach ($paths as $path) {
        $value = $data;
        $found = true;
        foreach ($path as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                $found = false;
                break;
            }
            $value = $value[$segment];
        }
        if ($found) {
            return $value;
        }
    }

    return null;
}
