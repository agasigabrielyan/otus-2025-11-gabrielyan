<?php

declare(strict_types=1);

final class BitrixOAuth
{
    private string $clientId;
    private string $clientSecret;

    public function __construct(string $clientId, string $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public static function fromConfigFile(string $path): self
    {
        if (!is_file($path)) {
            throw new RuntimeException('OAuth config not found: ' . $path);
        }

        $config = require $path;
        if (!is_array($config)) {
            throw new RuntimeException('OAuth config must return array');
        }

        $clientId = (string)($config['client_id'] ?? '');
        $clientSecret = (string)($config['client_secret'] ?? '');

        if ($clientId === '' || $clientSecret === '') {
            throw new RuntimeException('client_id and client_secret are required in config');
        }

        return new self($clientId, $clientSecret);
    }

    public function refreshToken(string $refreshToken): array
    {
        $payload = http_build_query([
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
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
            return [
                'ok' => false,
                'error' => 'OAUTH_REQUEST_FAILED',
                'error_description' => 'Cannot call Bitrix OAuth endpoint',
            ];
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return [
                'ok' => false,
                'error' => 'OAUTH_BAD_RESPONSE',
                'error_description' => $raw,
            ];
        }

        if (!empty($data['error'])) {
            return [
                'ok' => false,
                'error' => (string)$data['error'],
                'error_description' => (string)($data['error_description'] ?? ''),
            ];
        }

        return [
            'ok' => true,
            'access_token' => (string)($data['access_token'] ?? ''),
            'refresh_token' => (string)($data['refresh_token'] ?? ''),
            'expires_in' => (int)($data['expires_in'] ?? 0),
        ];
    }
}
