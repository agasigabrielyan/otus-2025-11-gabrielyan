<?php

namespace Otus\TaskManager\Installer;

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class TableInstaller
{
    const TABLE_TASK_COMMENTS = 'otus_task_comments';
    const TABLE_TASK_LOGS = 'otus_task_logs';
    const TABLE_TASK_USERS = 'otus_task_users';

    public static function install(): void
    {
        $connection = Application::getConnection();

        // --- Таблица комментариев ---
        $sql = "
            CREATE TABLE IF NOT EXISTS " . self::TABLE_TASK_COMMENTS . " (
                ID INT AUTO_INCREMENT PRIMARY KEY,
                TASK_ID INT NOT NULL,
                USER_ID INT NOT NULL,
                COMMENT TEXT NOT NULL,
                CREATED_AT DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";
        $connection->query($sql);

        // --- Таблица логов ---
        $sql = "
            CREATE TABLE IF NOT EXISTS " . self::TABLE_TASK_LOGS . " (
                ID INT AUTO_INCREMENT PRIMARY KEY,
                TASK_ID INT NOT NULL,
                ACTION VARCHAR(255) NOT NULL,
                USER_ID INT NOT NULL,
                CREATED_AT DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";
        $connection->query($sql);

        // --- Таблица пользователей задач ---
        $sql = "
            CREATE TABLE IF NOT EXISTS " . self::TABLE_TASK_USERS . " (
                ID INT AUTO_INCREMENT PRIMARY KEY,
                TASK_ID INT NOT NULL,
                USER_ID INT NOT NULL,
                ASSIGNED_AT DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";
        $connection->query($sql);
    }

    public static function uninstall(): void
    {
        $connection = Application::getConnection();

        $connection->query("DROP TABLE IF EXISTS " . self::TABLE_TASK_COMMENTS);
        $connection->query("DROP TABLE IF EXISTS " . self::TABLE_TASK_LOGS);
        $connection->query("DROP TABLE IF EXISTS " . self::TABLE_TASK_USERS);
    }
}