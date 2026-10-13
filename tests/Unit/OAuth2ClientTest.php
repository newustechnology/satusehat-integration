<?php

namespace Newus\Satusehat\Tests\Unit;

use Newus\Satusehat\OAuth2Client;
use Newus\Satusehat\Storage\MemoryTokenStorage;
use PHPUnit\Framework\TestCase;

class OAuth2ClientTest extends TestCase
{
    public function testConstructorAcceptsArrayConfigDirectly(): void
    {
        $client = new OAuth2Client([
            'environment' => 'DEV',
            'client_id' => 'dev_id',
            'client_secret' => 'dev_secret',
            'organization_id' => 'dev_org',
            'token_storage' => new MemoryTokenStorage(),
        ]);

        $this->assertSame('DEV', $client->satusehat_env);
        $this->assertSame('dev_id', $client->client_id);
        $this->assertSame('dev_org', $client->organization_id);
    }

    public function testTokenRetrievedFromCache(): void
    {
        $storage = new MemoryTokenStorage();
        $storage->set('DEV', 'dev_id', 'cached-jwt-token', 3000);

        $client = new OAuth2Client([
            'environment' => 'DEV',
            'client_id' => 'dev_id',
            'client_secret' => 'dev_secret',
            'organization_id' => 'dev_org',
            'token_storage' => $storage,
        ]);

        $this->assertSame('cached-jwt-token', $client->token());
    }

    public function testRespondErrorFormat(): void
    {
        $client = new OAuth2Client([
            'environment' => 'DEV',
            'client_id' => 'dev_id',
            'client_secret' => 'dev_secret',
            'organization_id' => 'dev_org',
        ]);

        $res = $client->respondError(['statusCode' => 404, 'res' => 'Not found']);
        $this->assertSame(404, $res[0]);
        $this->assertSame('Not found', $res[1]);
    }
}
