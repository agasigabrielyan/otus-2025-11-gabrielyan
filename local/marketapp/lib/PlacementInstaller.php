<?php

declare(strict_types=1);

final class PlacementInstaller
{
    private const PLACEMENT = 'CRM_DEAL_LIST_TOOLBAR';
    private const TITLE = 'CRM-счётчик';

    public function install(array $tenant, TenantRepository $repository): void
    {
        $handler = $this->buildHandlerUrl();

        $rest = new BitrixRest();
        $rest->bindPlacement($tenant, $repository, self::PLACEMENT, $handler, self::TITLE);
    }

    private function buildHandlerUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = (string)($_SERVER['HTTP_HOST'] ?? 'otusgabrielyan.ru');

        return $scheme . '://' . $host . '/local/marketapp/widget.php';
    }
}
