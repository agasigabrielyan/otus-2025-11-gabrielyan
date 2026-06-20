<?php

final class TenantStorage
{
    private string $dataDir;

    public function __construct(string $dataDir)
    {
        $this->dataDir = rtrim($dataDir, '/');
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0775, true);
        }
    }

    public function save(string $memberId, array $data): void
    {
        $file = $this->getFile($memberId);
        file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    }

    public function load(string $memberId): array
    {
        $file = $this->getFile($memberId);
        if (!is_file($file)) {
            return [];
        }

        $data = json_decode((string)file_get_contents($file), true);
        return is_array($data) ? $data : [];
    }

    private function getFile(string $memberId): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $memberId);
        return $this->dataDir . '/' . $safe . '.json';
    }
}