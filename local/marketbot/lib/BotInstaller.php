<?php

declare(strict_types=1);

final class BotInstaller
{
    public function install(array $tenant, TenantRepository $repository): int
    {
        $existingBotId = (int)($tenant['BOT_ID'] ?? 0);
        if ($existingBotId > 0) {
            return $existingBotId;
        }

        $config = AppConfig::load();
        $handlerUrl = $config['handler_url'];
        if ($handlerUrl === '') {
            throw new RuntimeException('В config.php укажите handler_url.');
        }

        $memberId = (string)($tenant['MEMBER_ID'] ?? '');
        $response = (new BitrixRest())->callRaw($tenant, 'imbot.register', [
            'CODE' => $config['bot_code'],
            'TYPE' => 'B',
            'EVENT_HANDLER' => $handlerUrl,
            'OPENLINE' => 'N',
            'CLIENT_ID' => $config['client_id'],
            'PROPERTIES' => [
                'NAME' => $config['bot_name'],
                'WORK_POSITION' => 'CRM-статистика по команде /stats',
            ],
        ], $repository);

        if (!empty($response['error'])) {
            $description = (string)($response['error_description'] ?? $response['error']);
            throw new RuntimeException('imbot.register: ' . $description);
        }

        $botId = (int)($response['result'] ?? 0);
        if ($botId <= 0) {
            throw new RuntimeException('imbot.register не вернул BOT_ID.');
        }

        $repository->saveBotId($memberId, $botId);

        return $botId;
    }
}
