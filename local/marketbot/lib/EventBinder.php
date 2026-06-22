<?php

declare(strict_types=1);

final class EventBinder
{
    public function bindBotEvents(array $tenant, TenantRepository $repository): void
    {
        $handlerUrl = AppConfig::load()['handler_url'];
        if ($handlerUrl === '') {
            throw new RuntimeException('В config.php укажите handler_url.');
        }

        $events = [
            'ONIMBOTMESSAGEADD',
            'ONIMBOTJOINCHAT',
        ];

        foreach ($events as $event) {
            $response = (new BitrixRest())->callRaw($tenant, 'event.bind', [
                'event' => $event,
                'handler' => $handlerUrl,
            ], $repository);

            if (!empty($response['error'])) {
                $description = (string)($response['error_description'] ?? $response['error']);
                throw new RuntimeException($event . ': ' . $description);
            }
        }
    }

    public function listEvents(array $tenant, TenantRepository $repository): array
    {
        $response = (new BitrixRest())->callRaw($tenant, 'event.get', [], $repository);
        if (!empty($response['error'])) {
            $description = (string)($response['error_description'] ?? $response['error']);
            throw new RuntimeException('event.get: ' . $description);
        }

        return is_array($response['result'] ?? null) ? $response['result'] : [];
    }
}
