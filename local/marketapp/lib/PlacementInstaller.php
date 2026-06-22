<?php

declare(strict_types=1);

final class PlacementInstaller
{
    /** @var array<string, string> */
    private const PLACEMENTS = [
        'CRM_DEAL_LIST_TOOLBAR' => 'CRM-счётчик',
        'CRM_DEAL_DETAIL_TAB' => 'CRM-счётчик',
    ];

    public function install(array $tenant, TenantRepository $repository): void
    {
        $handler = $this->buildHandlerUrl();
        $rest = new BitrixRest();

        foreach (self::PLACEMENTS as $placement => $title) {
            $rest->bindPlacement($tenant, $repository, $placement, $handler, $title);
        }
    }

    private function buildHandlerUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = (string)($_SERVER['HTTP_HOST'] ?? 'otusgabrielyan.ru');

        return $scheme . '://' . $host . '/local/marketapp/widget.php';
    }
}
