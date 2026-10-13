<?php

namespace Newus\Satusehat\Storage;

class FileLogStorage implements LogStorageInterface
{
    private string $filePath;

    public function __construct(?string $filePath = null)
    {
        $this->filePath = $filePath ?: (sys_get_temp_dir() . '/satusehat_' . date('Y-m-d') . '.log');
    }

    public function log(string $responseId, string $action, string $url, ?array $payload, $response): void
    {
        $entry = sprintf(
            "[%s] ID:%s ACTION:%s URL:%s PAYLOAD:%s RES:%s\n",
            date('Y-m-d H:i:s'),
            $responseId,
            $action,
            $url,
            $payload !== null ? json_encode($payload) : 'null',
            is_string($response) ? $response : json_encode($response)
        );
        @file_put_contents($this->filePath, $entry, FILE_APPEND | LOCK_EX);
    }
}
