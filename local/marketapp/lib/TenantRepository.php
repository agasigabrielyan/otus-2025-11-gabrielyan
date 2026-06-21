<?php

declare(strict_types=1);

use Bitrix\Main\Application;

final class TenantRepository
{
    private const TABLE = 'otus_marketapp_tenant';

    public function ensureTable(): void
    {
        $connection = Application::getConnection();

        if ($connection->isTableExists(self::TABLE)) {
            return;
        }

        $connection->queryExecute(
            'CREATE TABLE ' . self::TABLE . ' (
                ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
                MEMBER_ID VARCHAR(64) NOT NULL,
                DOMAIN VARCHAR(255) NOT NULL,
                AUTH_ID VARCHAR(512) NOT NULL,
                REFRESH_ID VARCHAR(512) NOT NULL,
                AUTH_EXPIRES_AT INT UNSIGNED NULL,
                INSTALLED_AT DATETIME NOT NULL,
                UPDATED_AT DATETIME NOT NULL,
                PRIMARY KEY (ID),
                UNIQUE KEY UX_MEMBER_ID (MEMBER_ID)
            )'
        );
    }

    public function save(string $memberId, array $data): void
    {
        $connection = Application::getConnection();
        $helper = $connection->getSqlHelper();
        $now = date('Y-m-d H:i:s');

        $fields = [
            'MEMBER_ID' => $memberId,
            'DOMAIN' => (string)($data['domain'] ?? ''),
            'AUTH_ID' => (string)($data['auth_id'] ?? ''),
            'REFRESH_ID' => (string)($data['refresh_id'] ?? ''),
            'UPDATED_AT' => $now,
        ];

        if (!empty($data['auth_expires_at'])) {
            $fields['AUTH_EXPIRES_AT'] = (int)$data['auth_expires_at'];
        }

        $existing = $this->load($memberId);

        if ($existing === null) {
            $fields['INSTALLED_AT'] = $now;
            $connection->add(self::TABLE, $fields);

            return;
        }

        $connection->update(
            self::TABLE,
            $fields,
            'WHERE MEMBER_ID = \'' . $helper->forSql($memberId) . '\''
        );
    }

    public function load(string $memberId): ?array
    {
        $connection = Application::getConnection();
        $helper = $connection->getSqlHelper();

        $result = $connection->query(
            'SELECT * FROM ' . self::TABLE
            . ' WHERE MEMBER_ID = \'' . $helper->forSql($memberId) . '\''
            . ' LIMIT 1'
        );

        $row = $result->fetch();

        return is_array($row) ? $row : null;
    }

    public function countAll(): int
    {
        $connection = Application::getConnection();
        $result = $connection->query('SELECT COUNT(*) AS CNT FROM ' . self::TABLE);
        $row = $result->fetch();

        return (int)($row['CNT'] ?? 0);
    }
}
