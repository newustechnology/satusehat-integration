<?php

namespace Newus\Satusehat\Storage;

class FileTokenStorage implements TokenStorageInterface
{
    private string $directory;

    public function __construct(?string $directory = null)
    {
        $this->directory = rtrim($directory ?: (sys_get_temp_dir() . '/satusehat_tokens'), '/');
        if (! is_dir($this->directory)) {
            @mkdir($this->directory, 0755, true);
        }
    }

    private function getFilePath(string $environment, string $clientId): string
    {
        $hash = md5($environment . '_' . $clientId);
        return $this->directory . '/token_' . $hash . '.json';
    }

    public function get(string $environment, string $clientId): ?string
    {
        $file = $this->getFilePath($environment, $clientId);
        if (! file_exists($file)) {
            return null;
        }

        $content = @file_get_contents($file);
        if (! $content) {
            return null;
        }

        $data = json_decode($content, true);
        if (! is_array($data) || ! isset($data['token'], $data['expires_at'])) {
            @unlink($file);
            return null;
        }

        if (time() >= (int) $data['expires_at']) {
            @unlink($file);
            return null;
        }

        return (string) $data['token'];
    }

    public function set(string $environment, string $clientId, string $token, int $ttl = 3000): void
    {
        $file = $this->getFilePath($environment, $clientId);
        $data = [
            'token' => $token,
            'expires_at' => time() + $ttl,
            'updated_at' => time(),
        ];
        @file_put_contents($file, json_encode($data), LOCK_EX);
    }

    public function forget(string $environment, string $clientId): void
    {
        $file = $this->getFilePath($environment, $clientId);
        if (file_exists($file)) {
            @unlink($file);
        }
    }
}
