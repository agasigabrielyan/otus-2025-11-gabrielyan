<?php

declare(strict_types=1);

final class TenantAuth
{
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

    private static function resolveExpiresAt(mixed $expiresIn): ?int
    {
        $seconds = (int)$expiresIn;

        if ($seconds <= 0) {
            return null;
        }

        // expires_in — секунды от «сейчас»; большие числа — уже unix-time
        if ($seconds > 1000000000) {
            return $seconds;
        }

        return time() + $seconds;
    }
}
