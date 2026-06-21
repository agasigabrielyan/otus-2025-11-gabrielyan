<?php

declare(strict_types=1);

final class AppConfig
{
    public static function load(): array
    {
        $path = dirname(__DIR__) . '/config.php';

        if (!is_file($path)) {
            throw new RuntimeException(
                'Файл config.php не найден. Скопируйте config.example.php в config.php и укажите client_id и client_secret.'
            );
        }

        $config = require $path;

        if (!is_array($config)) {
            throw new RuntimeException('config.php должен возвращать массив.');
        }

        $clientId = trim((string)($config['client_id'] ?? ''));
        $clientSecret = trim((string)($config['client_secret'] ?? ''));

        if ($clientId === '' || $clientSecret === '') {
            throw new RuntimeException('В config.php нужно указать client_id и client_secret.');
        }

        return [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ];
    }
}
