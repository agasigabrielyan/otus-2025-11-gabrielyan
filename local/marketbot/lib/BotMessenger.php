<?php

declare(strict_types=1);

final class BotMessenger
{
    public function sendToUser(array $tenant, TenantRepository $repository, int $userId, string $message): void
    {
        $botId = (int)($tenant['BOT_ID'] ?? 0);
        if ($botId <= 0 || $userId <= 0) {
            throw new RuntimeException('Нет BOT_ID или USER_ID.');
        }

        $response = (new BitrixRest())->callRaw($tenant, 'imbot.message.add', [
            'BOT_ID' => $botId,
            'DIALOG_ID' => $userId,
            'MESSAGE' => $message,
        ], $repository);

        if (!empty($response['error'])) {
            $description = (string)($response['error_description'] ?? $response['error']);
            throw new RuntimeException('imbot.message.add: ' . $description);
        }
    }

    public function getCurrentUserId(array $tenant, TenantRepository $repository): int
    {
        $response = (new BitrixRest())->callRaw($tenant, 'user.current', [], $repository);
        if (!empty($response['error'])) {
            $description = (string)($response['error_description'] ?? $response['error']);
            throw new RuntimeException('user.current: ' . $description);
        }

        return (int)($response['result']['ID'] ?? 0);
    }
}
