<?php

declare(strict_types=1);

final class BitrixRest
{
    public function getDealCount(array $tenant, TenantRepository $repository): int
    {
        $response = $this->call($tenant, 'crm.deal.list', [
            'select' => ['ID'],
            'order' => ['ID' => 'DESC'],
        ], $repository);

        if (isset($response['total'])) {
            return (int)$response['total'];
        }

        return count($response['result'] ?? []);
    }

    private function call(
        array $tenant,
        string $method,
        array $params,
        TenantRepository $repository
    ): array {
        $domain = (string)($tenant['DOMAIN'] ?? '');
        $authId = (string)($tenant['AUTH_ID'] ?? '');

        if ($domain === '' || $authId === '') {
            throw new RuntimeException('У портала нет DOMAIN или AUTH_ID.');
        }

        $data = $this->request($domain, $method, $params, $authId);

        if (($data['error'] ?? '') === 'expired_token') {
            $memberId = (string)($tenant['MEMBER_ID'] ?? '');
            $this->refreshToken($memberId, $tenant, $repository);
            $tenant = $repository->load($memberId);

            if ($tenant === null) {
                throw new RuntimeException('Не удалось обновить токен портала.');
            }

            $data = $this->request(
                (string)$tenant['DOMAIN'],
                $method,
                $params,
                (string)$tenant['AUTH_ID']
            );
        }

        if (!empty($data['error'])) {
            $description = (string)($data['error_description'] ?? $data['error']);
            throw new RuntimeException($description);
        }

        return $data;
    }

    private function request(string $domain, string $method, array $params, string $authId): array
    {
        $url = 'https://' . $domain . '/rest/' . $method . '.json';
        $params['auth'] = $authId;

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($params),
                'timeout' => 20,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        if ($raw === false) {
            return [
                'error' => 'HTTP_REQUEST_FAILED',
                'error_description' => 'Не удалось выполнить REST-запрос к порталу',
            ];
        }

        $data = json_decode($raw, true);

        return is_array($data) ? $data : [
            'error' => 'BAD_RESPONSE',
            'error_description' => $raw,
        ];
    }

    private function refreshToken(string $memberId, array $tenant, TenantRepository $repository): void
    {
        $refreshId = (string)($tenant['REFRESH_ID'] ?? '');
        if ($memberId === '' || $refreshId === '') {
            throw new RuntimeException('Нет refresh_token для обновления access_token.');
        }

        $config = AppConfig::load();
        $payload = http_build_query([
            'grant_type' => 'refresh_token',
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'refresh_token' => $refreshId,
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $payload,
                'timeout' => 20,
                'ignore_errors' => true,
            ],
        ]);

        $raw = @file_get_contents('https://oauth.bitrix.info/oauth/token/', false, $context);
        if ($raw === false) {
            throw new RuntimeException('Не удалось обновить токен через OAuth.');
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || !empty($data['error'])) {
            $message = (string)($data['error_description'] ?? $data['error'] ?? 'OAuth refresh failed');
            throw new RuntimeException($message);
        }

        $repository->save($memberId, [
            'domain' => (string)($tenant['DOMAIN'] ?? ''),
            'auth_id' => (string)($data['access_token'] ?? ''),
            'refresh_id' => (string)($data['refresh_token'] ?? $refreshId),
            'auth_expires_at' => time() + (int)($data['expires_in'] ?? 3600),
        ]);
    }
}
