<?php

namespace Newus\Satusehat\Tests\Unit;

use Newus\Satusehat\Exception\Helper\OAuth2ClientException;
use Newus\Satusehat\OAuth2Client;
use Newus\Satusehat\Storage\MemoryTokenStorage;
use PHPUnit\Framework\TestCase;

class OAuth2ClientTest extends TestCase
{
    public function testConstructorAcceptsArrayConfigDirectly(): void
    {
        $client = new OAuth2Client([
            'environment' => 'STG',
            'client_id' => 'stg_id',
            'client_secret' => 'stg_secret',
            'organization_id' => 'stg_org',
            'token_storage' => new MemoryTokenStorage(),
        ]);

        $this->assertSame('STG', $client->satusehat_env);
        $this->assertSame('stg_id', $client->client_id);
        $this->assertSame('stg_org', $client->organization_id);
    }

    public function testSandboxEnvironmentNormalizedToStg(): void
    {
        $client = new OAuth2Client([
            'environment' => 'SANDBOX',
            'client_id' => 'stg_id',
            'client_secret' => 'stg_secret',
            'organization_id' => 'stg_org',
            'token_storage' => new MemoryTokenStorage(),
        ]);

        $this->assertSame('STG', $client->satusehat_env);
        $this->assertSame('https://api-satusehat-stg.dto.kemkes.go.id', $client->base_url);
    }

    public function testDevEnvironmentThrowsException(): void
    {
        $this->expectException(OAuth2ClientException::class);
        $this->expectExceptionMessage('SATUSEHAT environment invalid, supported (STG, PROD). DEV given.');

        new OAuth2Client([
            'environment' => 'DEV',
            'client_id' => 'dev_id',
            'client_secret' => 'dev_secret',
            'organization_id' => 'dev_org',
        ]);
    }

    public function testTokenRetrievedFromCache(): void
    {
        $storage = new MemoryTokenStorage();
        $storage->set('STG', 'stg_id', 'cached-jwt-token', 3000);

        $client = new OAuth2Client([
            'environment' => 'STG',
            'client_id' => 'stg_id',
            'client_secret' => 'stg_secret',
            'organization_id' => 'stg_org',
            'token_storage' => $storage,
        ]);

        $this->assertSame('cached-jwt-token', $client->token());
    }

    public function testRespondErrorFormat(): void
    {
        $client = new OAuth2Client([
            'environment' => 'STG',
            'client_id' => 'stg_id',
            'client_secret' => 'stg_secret',
            'organization_id' => 'stg_org',
        ]);

        $res = $client->respondError(['statusCode' => 404, 'res' => 'Not found']);
        $this->assertSame(404, $res[0]);
        $this->assertSame('Not found', $res[1]);
    }

    public function testSandboxPatientDataAndLookup(): void
    {
        $client = new OAuth2Client([
            'environment' => 'STG',
            'client_id' => 'stg_id',
            'client_secret' => 'stg_secret',
            'organization_id' => 'stg_org',
        ]);

        $this->assertCount(10, $client->patient_dev);
        $this->assertCount(10, $client->getPatientSandbox());

        // Find by NIK
        $patient = $client->findPatientSandboxByNik('9271060312000001');
        $this->assertNotNull($patient);
        $this->assertSame('Ardianto Putra', $patient['name']);
        $this->assertSame('male', $patient['gender']);
        $this->assertSame('1992-01-09', $patient['birthDate']);
        $this->assertSame('P02478375538', $patient['ihs_number']);

        // Find by IHS
        $patientByIhs = $client->findPatientSandboxByIhs('P02280547535');
        $this->assertNotNull($patientByIhs);
        $this->assertSame('Salsabilla Anjani Rizki', $patientByIhs['name']);
        $this->assertSame('9104025209000006', $patientByIhs['nik']);

        // Non-existent
        $this->assertNull($client->findPatientSandboxByNik('0000000000000000'));
        $this->assertNull($client->findPatientSandboxByIhs('P99999999999'));
    }

    public function testSandboxPractitionerDataAndLookup(): void
    {
        $client = new OAuth2Client([
            'environment' => 'STG',
            'client_id' => 'stg_id',
            'client_secret' => 'stg_secret',
            'organization_id' => 'stg_org',
        ]);

        $this->assertCount(10, $client->practitioner_dev);
        $this->assertCount(10, $client->getPractitionerSandbox());

        // Find by NIK
        $doc = $client->findPractitionerSandboxByNik('7209061211900001');
        $this->assertNotNull($doc);
        $this->assertSame('dr. Alexander', $doc['name']);
        $this->assertSame('male', $doc['gender']);
        $this->assertSame('1994-01-01', $doc['birthDate']);
        $this->assertSame('10009880728', $doc['ihs_number']);

        // Find by IHS
        $docByIhs = $client->findPractitionerSandboxByIhs('10002074224');
        $this->assertNotNull($docByIhs);
        $this->assertSame('dr. Olivia Kirana, Sp.OG', $docByIhs['name']);
        $this->assertSame('3217040109800006', $docByIhs['nik']);

        // Non-existent
        $this->assertNull($client->findPractitionerSandboxByNik('0000000000000000'));
        $this->assertNull($client->findPractitionerSandboxByIhs('99999999999'));
    }
}
