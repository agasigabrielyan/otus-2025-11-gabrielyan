<?php

declare(strict_types=1);

final class TenantAuth
{
    public static function getInput(): array
    {
        $input = $_REQUEST;

        if ($_POST !== []) {
            $input = array_replace_recursive($input, $_POST);
        }

        $raw = file_get_contents('php://input');
        if (is_string($raw) && trim($raw) !== '') {
            $json = json_decode($raw, true);
            if (is_array($json)) {
                $input = array_replace_recursive($input, $json);
            }
        }

        return $input;
    }

    /**
     * Два формата от Bitrix24:
     * 1) ONAPPINSTALL → auth[member_id], auth[access_token], ...
     * 2) Открытие в iframe → member_id, AUTH_ID, REFRESH_ID, DOMAIN
     */
    public static function fromRequest(array $request): array
    {
        if (isset($request['auth']) && is_array($request['auth'])) {
            $auth = $request['auth'];

            return [
                'member_id' => trim((string)($auth['member_id'] ?? '')),
                'domain' => (string)($auth['domain'] ?? ''),
                'auth_id' => (string)($auth['access_token'] ?? ''),
                'refresh_id' => (string)($auth['refresh_token'] ?? ''),
                'auth_expires_at' => self::resolveExpiresAt($auth['expires_in'] ?? null),
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
