<?php

declare(strict_types=1);

final class TenantRepository
{
    private const TABLE = 'otus_marketbot_tenant';

    public function ensureTable(): void
    {
        $db = Database::connection();
        $table = self::TABLE;

        $result = $db->query("SHOW TABLES LIKE '{$table}'");
        if ($result === false || $result->num_rows === 0) {
            $db->query(
                "CREATE TABLE {$table} (
                    ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    MEMBER_ID VARCHAR(64) NOT NULL,
                    DOMAIN VARCHAR(255) NOT NULL,
                    AUTH_ID VARCHAR(512) NOT NULL,
                    REFRESH_ID VARCHAR(512) NOT NULL,
                    AUTH_EXPIRES_AT INT UNSIGNED NULL,
                    BOT_ID INT UNSIGNED NULL,
                    INSTALLED_AT DATETIME NOT NULL,
                    UPDATED_AT DATETIME NOT NULL,
                    PRIMARY KEY (ID),
                    UNIQUE KEY UX_MEMBER_ID (MEMBER_ID)
                )"
            );

            if ($db->error) {
                throw new RuntimeException('Не удалось создать таблицу: ' . $db->error);
            }

            return;
        }

        $column = $db->query("SHOW COLUMNS FROM {$table} LIKE 'BOT_ID'");
        if ($column !== false && $column->num_rows === 0) {
            $db->query("ALTER TABLE {$table} ADD COLUMN BOT_ID INT UNSIGNED NULL AFTER AUTH_EXPIRES_AT");
        }
    }

    public function save(string $memberId, array $data): void
    {
        $existing = $this->load($memberId);
        $now = date('Y-m-d H:i:s');
        $domain = (string)($data['domain'] ?? '');
        $authId = (string)($data['auth_id'] ?? '');
        $refreshId = (string)($data['refresh_id'] ?? '');
        $expiresAt = !empty($data['auth_expires_at']) ? (int)$data['auth_expires_at'] : null;

        if ($existing === null) {
            $stmt = Database::connection()->prepare(
                'INSERT INTO ' . self::TABLE . ' (MEMBER_ID, DOMAIN, AUTH_ID, REFRESH_ID, AUTH_EXPIRES_AT, INSTALLED_AT, UPDATED_AT)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param(
                'ssssiss',
                $memberId,
                $domain,
                $authId,
                $refreshId,
                $expiresAt,
                $now,
                $now
            );
            $stmt->execute();

            if ($stmt->error) {
                throw new RuntimeException('Не удалось сохранить tenant: ' . $stmt->error);
            }

            return;
        }

        $stmt = Database::connection()->prepare(
            'UPDATE ' . self::TABLE . '
             SET DOMAIN = ?, AUTH_ID = ?, REFRESH_ID = ?, AUTH_EXPIRES_AT = ?, UPDATED_AT = ?
             WHERE MEMBER_ID = ?'
        );
        $stmt->bind_param(
            'sssiss',
            $domain,
            $authId,
            $refreshId,
            $expiresAt,
            $now,
            $memberId
        );
        $stmt->execute();

        if ($stmt->error) {
            throw new RuntimeException('Не удалось обновить tenant: ' . $stmt->error);
        }
    }

    public function load(string $memberId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM ' . self::TABLE . ' WHERE MEMBER_ID = ? LIMIT 1'
        );
        $stmt->bind_param('s', $memberId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return is_array($row) ? $row : null;
    }

    public function countAll(): int
    {
        $result = Database::connection()->query('SELECT COUNT(*) AS CNT FROM ' . self::TABLE);
        $row = $result ? $result->fetch_assoc() : null;

        return (int)($row['CNT'] ?? 0);
    }

    public function saveBotId(string $memberId, int $botId): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE ' . self::TABLE . ' SET BOT_ID = ?, UPDATED_AT = ? WHERE MEMBER_ID = ?'
        );
        $now = date('Y-m-d H:i:s');
        $stmt->bind_param('iss', $botId, $now, $memberId);
        $stmt->execute();

        if ($stmt->error) {
            throw new RuntimeException('Не удалось сохранить BOT_ID: ' . $stmt->error);
        }
    }
}
