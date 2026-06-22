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
    $auth = TenantAuth::fromRequest($input);
    if (TenantAuth::canSave($auth)) {
        $repository->save($auth['member_id'], $auth);
    }

    $memberId = resolveMemberId($input, $auth);
    if ($memberId === '') {
        http_response_code(200);
        echo 'skip: no member_id';
        exit;
    }

    $tenant = $repository->load($memberId);
    if ($tenant === null) {
        http_response_code(200);
        echo 'skip: tenant not found';
        exit;
    }

    $dialogId = resolveDialogId($input);
    if ($dialogId === '') {
        http_response_code(200);
        echo 'skip: no dialog_id';
        exit;
    }

    $text = trim(resolveMessageText($input));
    $botId = (int)($tenant['BOT_ID'] ?? 0);
    if ($botId <= 0) {
        http_response_code(200);
        echo 'skip: bot not registered';
        exit;
    }

    $restTenant = [
        'MEMBER_ID' => (string)$tenant['MEMBER_ID'],
        'DOMAIN' => (string)$tenant['DOMAIN'],
        'AUTH_ID' => (string)$tenant['AUTH_ID'],
        'REFRESH_ID' => (string)$tenant['REFRESH_ID'],
        'BOT_ID' => $botId,
    ];

    $reply = buildReplyText($text, $restTenant, $repository);

    (new BitrixRest())->callRaw($restTenant, 'imbot.message.add', [
        'BOT_ID' => $botId,
        'DIALOG_ID' => $dialogId,
        'MESSAGE' => $reply,
    ], $repository);

    http_response_code(200);
    echo 'ok';
} catch (Throwable $e) {
    http_response_code(500);
    echo 'error: ' . $e->getMessage();
}

function resolveMemberId(array $input, array $auth): string
{
    if (!empty($auth['member_id'])) {
        return (string)$auth['member_id'];
    }

    $value = readByPaths($input, [
        ['member_id'],
        ['auth', 'member_id'],
        ['data', 'PARAMS', 'FROM_USER_ID'],
        ['data', 'PARAMS', 'USER_ID'],
    ]);

    return is_scalar($value) ? trim((string)$value) : '';
}

function resolveDialogId(array $input): string
{
    $value = readByPaths($input, [
        ['data', 'PARAMS', 'DIALOG_ID'],
        ['data', 'PARAMS', 'TO_CHAT_ID'],
        ['DIALOG_ID'],
    ]);

    if ($value === null || $value === '') {
        return '';
    }

    if (is_numeric($value)) {
        return (string)$value;
    }

    return trim((string)$value);
}

function resolveMessageText(array $input): string
{
    $value = readByPaths($input, [
        ['data', 'PARAMS', 'MESSAGE'],
        ['data', 'PARAMS', 'MESSAGE_TEXT'],
        ['MESSAGE'],
    ]);

    return is_scalar($value) ? (string)$value : '';
}

function buildReplyText(string $text, array $tenant, TenantRepository $repository): string
{
    $normalized = trim($text);
    $command = function_exists('mb_strtolower') ? mb_strtolower($normalized) : strtolower($normalized);

    if ($command === '/stats' || $command === 'stats' || $command === 'статистика') {
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
