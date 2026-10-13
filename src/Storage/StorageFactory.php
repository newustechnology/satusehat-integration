<?php

namespace Newus\Satusehat\Storage;

class StorageFactory
{
    public static function createTokenStorage(?string $driver = null): TokenStorageInterface
    {
        if ($driver === 'memory') {
            return new MemoryTokenStorage();
        }

        if ($driver === 'file') {
            return new FileTokenStorage();
        }

        // Automatic framework detection:
        // If Laravel Cache exists and is bound, or fallback to FileTokenStorage
        return new FileTokenStorage();
    }

    public static function createLogStorage(?string $driver = null): LogStorageInterface
    {
        if ($driver === 'file') {
            return new FileLogStorage();
        }

        return new NullLogStorage();
    }
}
