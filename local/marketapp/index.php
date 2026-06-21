<?php

declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CRM-счётчик</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 16px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        .app {
            max-width: 480px;
            margin: 0 auto;
        }
        .app__title {
            margin: 0 0 16px;
            font-size: 20px;
            font-weight: 600;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
        }
        .card__label {
            margin: 0 0 8px;
            font-size: 13px;
            color: #828b95;
        }
        .card__value {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            color: #2fc6f6;
        }
        .stub {
            margin: 12px 0 0;
            font-size: 13px;
            color: #828b95;
        }
    </style>
</head>
<body>
<div class="app">
    <h1 class="app__title">CRM-счётчик</h1>
    <div class="card">
        <p class="card__label">Сделок в CRM</p>
        <p class="card__value">—</p>
        <p class="stub">Заглушка. Данные появятся после подключения REST API.</p>
    </div>
</div>
</body>
</html>
