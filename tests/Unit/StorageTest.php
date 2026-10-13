<?php

namespace Newus\Satusehat\Tests\Unit;

use Newus\Satusehat\Storage\FileTokenStorage;
use Newus\Satusehat\Storage\MemoryTokenStorage;
use Newus\Satusehat\Storage\NullLogStorage;
use Newus\Satusehat\Storage\StorageFactory;
use PHPUnit\Framework\TestCase;

class StorageTest extends TestCase
{
    public function testMemoryTokenStorage(): void
    {
        $storage = new MemoryTokenStorage();
        MemoryTokenStorage::clear();

        $this->assertNull($storage->get('DEV', 'client-1'));

        $storage->set('DEV', 'client-1', 'token-abc', 3000);
        $this->assertSame('token-abc', $storage->get('DEV', 'client-1'));

        $storage->forget('DEV', 'client-1');
        $this->assertNull($storage->get('DEV', 'client-1'));
    }

    public function testFileTokenStorage(): void
    {
        $dir = sys_get_temp_dir() . '/satusehat_test_' . uniqid();
        $storage = new FileTokenStorage($dir);

        $this->assertNull($storage->get('STG', 'client-2'));

        $storage->set('STG', 'client-2', 'token-xyz', 3000);
        $this->assertSame('token-xyz', $storage->get('STG', 'client-2'));

        $storage->forget('STG', 'client-2');
        $this->assertNull($storage->get('STG', 'client-2'));

        // Clean up
        @rmdir($dir);
    }

    public function testStorageFactoryResolvesStandalone(): void
    {
        $tokenStorage = StorageFactory::createTokenStorage();
        $this->assertNotNull($tokenStorage);

        $logStorage = StorageFactory::createLogStorage();
        $this->assertInstanceOf(NullLogStorage::class, $logStorage);
    }
}
