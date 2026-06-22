<?php

declare(strict_types=1);

final class EventBinder
{
    public function bindBotEvents(array $tenant, TenantRepository $repository, ?string $handlerUrl = null): void
    {
        $handlerUrl = $handlerUrl ?? $this->getHandlerUrl();
        $this->rebindEvent($tenant, $repository, 'ONIMBOTMESSAGEADD', $handlerUrl);
        $this->rebindEvent($tenant, $repository, 'ONIMBOTJOINCHAT', $handlerUrl);
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

    private function rebindEvent(
        array $tenant,
        TenantRepository $repository,
        string $eventName,
        string $handlerUrl
    ): void {
        $existing = $this->findHandlerUrl($tenant, $repository, $eventName);

        if ($existing !== null && $this->isSameHandler($existing, $handlerUrl)) {
            return;
        }

        if ($existing !== null) {
            $this->unbind($tenant, $repository, $eventName, $existing);
        }

        $response = (new BitrixRest())->callRaw($tenant, 'event.bind', [
            'event' => $eventName,
            'handler' => $handlerUrl,
        ], $repository);

        if (!empty($response['error'])) {
            $description = (string)($response['error_description'] ?? $response['error']);
            if (str_contains($description, 'already binded')) {
                return;
            }
            throw new RuntimeException($eventName . ': ' . $description);
        }
    }

    private function findHandlerUrl(array $tenant, TenantRepository $repository, string $eventName): ?string
    {
        foreach ($this->listEvents($tenant, $repository) as $event) {
            if (!is_array($event)) {
                continue;
            }
            if (mb_strtoupper((string)($event['event'] ?? '')) === $eventName) {
                $handler = trim((string)($event['handler'] ?? ''));
                return $handler !== '' ? $handler : null;
            }
        }

        return null;
    }

    private function unbind(
        array $tenant,
        TenantRepository $repository,
        string $eventName,
        string $handlerUrl
    ): void {
        (new BitrixRest())->callRaw($tenant, 'event.unbind', [
            'event' => $eventName,
            'handler' => $handlerUrl,
        ], $repository);
    }

    private function getHandlerUrl(): string
    {
        $handlerUrl = AppConfig::load()['handler_url'];
        if ($handlerUrl === '') {
            throw new RuntimeException('В config.php укажите handler_url.');
        }

        return $handlerUrl;
    }

    private function isSameHandler(string $existing, string $expected): bool
    {
        return rtrim($existing, '/') === rtrim($expected, '/')
            || str_starts_with($existing, rtrim($expected, '/') . '?');
    }
}
