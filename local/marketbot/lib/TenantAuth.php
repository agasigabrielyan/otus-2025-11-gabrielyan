<?php

declare(strict_types=1);

final class TenantAuth
{
    public static function getInput(?string $rawBody = null): array
    {
        $input = $_GET;

        if ($_POST !== []) {
            $input = array_replace_recursive($input, $_POST);
        }

        if ($rawBody === null) {
            $rawBody = file_get_contents('php://input');
        }

        if (!is_string($rawBody) || trim($rawBody) === '') {
            return $input;
        }

        $json = json_decode($rawBody, true);
        if (is_array($json)) {
            return array_replace_recursive($input, $json);
        }

        $contentType = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? ''));
        if (str_contains($contentType, 'application/x-www-form-urlencoded') || str_contains($rawBody, '=')) {
            $parsed = [];
            parse_str($rawBody, $parsed);
            if (is_array($parsed) && $parsed !== []) {
                return array_replace_recursive($input, $parsed);
            }
        }

        return $input;
    }

    public static function fromRequest(array $request): array
    {
        if (isset($request['auth']) && is_array($request['auth'])) {
            $auth = $request['auth'];

            return [
                'member_id' => trim((string)($auth['member_id'] ?? '')),
                'domain' => (string)($auth['domain'] ?? ''),
                'auth_id' => (string)($auth['access_token'] ?? ''),
                'refresh_id' => (string)($auth['refresh_token'] ?? ''),
                'auth_expires_at' => self::resolveExpiresAt($auth['expires_in'] ?? $auth['expires'] ?? null),
            ];
        }

        if (isset($request['access_token'])) {
            return [
                'member_id' => trim((string)($request['member_id'] ?? '')),
                'domain' => (string)($request['domain'] ?? ''),
                'auth_id' => (string)($request['access_token'] ?? ''),
                'refresh_id' => (string)($request['refresh_token'] ?? ''),
                'auth_expires_at' => self::resolveExpiresAt($request['expires_in'] ?? $request['expires'] ?? null),
            ];
        }

        return [
            'member_id' => trim((string)($request['member_id'] ?? '')),
            'domain' => (string)($request['DOMAIN'] ?? $request['domain'] ?? ''),
            'auth_id' => (string)($request['AUTH_ID'] ?? ''),
            'refresh_id' => (string)($request['REFRESH_ID'] ?? ''),
            'auth_expires_at' => (int)($request['AUTH_EXPIRES'] ?? 0) ?: null,
        ];
    }

    public static function canSave(array $auth): bool
    {
        return $auth['member_id'] !== '' && $auth['auth_id'] !== '';
    }

    private static function resolveExpiresAt(mixed $expiresIn): ?int
    {
        $seconds = (int)$expiresIn;

        if ($seconds <= 0) {
            return null;
        }

        if ($seconds > 1000000000) {
            return $seconds;
        }

        return time() + $seconds;
    }
}
