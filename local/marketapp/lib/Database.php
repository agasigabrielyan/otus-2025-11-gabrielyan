<?php

declare(strict_types=1);

final class Database
{
    private static ?mysqli $connection = null;

    public static function connection(): mysqli
    {
        if (self::$connection instanceof mysqli) {
            return self::$connection;
        }

        $settingsPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/.settings.php';
        if (!is_file($settingsPath)) {
            throw new RuntimeException('Не найден bitrix/.settings.php');
        }

        $settings = require $settingsPath;
        $db = $settings['connections']['value']['default'] ?? null;

        if (!is_array($db)) {
            throw new RuntimeException('Не найдены настройки подключения к БД');
        }

        $connection = new mysqli(
            (string)($db['host'] ?? 'localhost'),
            (string)($db['login'] ?? ''),
            (string)($db['password'] ?? ''),
            (string)($db['database'] ?? '')
        );

        if ($connection->connect_error) {
            throw new RuntimeException('Ошибка подключения к MySQL: ' . $connection->connect_error);
        }

        $connection->set_charset('utf8mb4');
        self::$connection = $connection;

        return self::$connection;
    }
}
