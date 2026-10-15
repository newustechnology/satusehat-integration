<?php

namespace Newus\Satusehat;

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Newus\Satusehat\Exception\Helper\OAuth2ClientException;
use Newus\Satusehat\Http\HttpClientFactory;
use Newus\Satusehat\Storage\LogStorageInterface;
use Newus\Satusehat\Storage\MemoryTokenStorage;
use Newus\Satusehat\Storage\StorageFactory;
use Newus\Satusehat\Storage\TokenStorageInterface;

function ss_base_path(string $path = ''): string
{
    if (function_exists('base_path')) {
        return base_path($path);
    }
    return rtrim(getcwd() ?: __DIR__, '/') . ($path ? '/' . ltrim($path, '/') : '');
}

function ss_config(string $key, $default = null)
{
    if (function_exists('config')) {
        return config($key, $default);
    }
    return $default;
}

class OAuth2Client
{
    /** @var string[] */
    public array $patient_dev = [
        'P02478375538',
        'P03647103112',
        'P00805884304',
        'P00912894463',
        'P01654557057',
        'P02280547535',
        'P01836748436',
        'P00883356749',
        'P01058967035',
        'P02428473601',
    ];

    /**
     * Data Pasien Dummy Resmi SATUSEHAT Sandbox DTO Kemenkes RI
     *
     * @var array<int, array<string, string>>
     */
    public array $patient_sandbox = [
        [
            'nik' => '9271060312000001',
            'name' => 'Ardianto Putra',
            'gender' => 'male',
            'birthDate' => '1992-01-09',
            'ihs_number' => 'P02478375538',
        ],
        [
            'nik' => '9204014804000002',
            'name' => 'Claudia Sintia',
            'gender' => 'female',
            'birthDate' => '1989-11-03',
            'ihs_number' => 'P03647103112',
        ],
        [
            'nik' => '9104224509000003',
            'name' => 'Elizabeth Dior',
            'gender' => 'female',
            'birthDate' => '1976-07-07',
            'ihs_number' => 'P00805884304',
        ],
        [
            'nik' => '9104223107000004',
            'name' => 'Dr. Alan Bagus Prasetya',
            'gender' => 'male',
            'birthDate' => '1977-09-03',
            'ihs_number' => 'P00912894463',
        ],
        [
            'nik' => '9104224608000005',
            'name' => 'Ghina Assyifa',
            'gender' => 'female',
            'birthDate' => '2004-08-21',
            'ihs_number' => 'P01654557057',
        ],
        [
            'nik' => '9104025209000006',
            'name' => 'Salsabilla Anjani Rizki',
            'gender' => 'female',
            'birthDate' => '2001-04-16',
            'ihs_number' => 'P02280547535',
        ],
        [
            'nik' => '9201076001000007',
            'name' => 'Theodore Elisjah',
            'gender' => 'female',
            'birthDate' => '1985-09-18',
            'ihs_number' => 'P01836748436',
        ],
        [
            'nik' => '9201394901000008',
            'name' => 'Sonia Herdianti',
            'gender' => 'female',
            'birthDate' => '1996-06-08',
            'ihs_number' => 'P00883356749',
        ],
        [
            'nik' => '9201076407000009',
            'name' => 'Nancy Wang',
            'gender' => 'female',
            'birthDate' => '1955-10-10',
            'ihs_number' => 'P01058967035',
        ],
        [
            'nik' => '9210060207000010',
            'name' => 'Syarif Muhammad',
            'gender' => 'male',
            'birthDate' => '1988-11-02',
            'ihs_number' => 'P02428473601',
        ],
    ];

    /** @var string[] */
    public array $practitioner_dev = ['10009880728', '10006926841', '10001354453', '10010910332', '10018180913', '10002074224', '10012572188', '10018452434', '10014058550', '10001915884'];

    /**
     * Data Dokter / Nakes (Practitioner) Dummy Resmi SATUSEHAT Sandbox DTO Kemenkes RI
     *
     * @var array<int, array<string, string>>
     */
    public array $practitioner_sandbox = [
        [
            'nik' => '7209061211900001',
            'name' => 'dr. Alexander',
            'gender' => 'male',
            'birthDate' => '1994-01-01',
            'ihs_number' => '10009880728',
        ],
        [
            'nik' => '3322071302900002',
            'name' => 'dr. Yoga Yandika, Sp.A',
            'gender' => 'male',
            'birthDate' => '1995-02-02',
            'ihs_number' => '10006926841',
        ],
        [
            'nik' => '3171071609900003',
            'name' => 'dr. Syarifuddin, Sp.Pd.',
            'gender' => 'male',
            'birthDate' => '1988-03-03',
            'ihs_number' => '10001354453',
        ],
        [
            'nik' => '3207192310600004',
            'name' => 'dr. Nicholas Evan, Sp.B.',
            'gender' => 'male',
            'birthDate' => '1986-04-04',
            'ihs_number' => '10010910332',
        ],
        [
            'nik' => '6408130207800005',
            'name' => 'dr. Dito Arifin, Sp.M.',
            'gender' => 'male',
            'birthDate' => '1985-05-05',
            'ihs_number' => '10018180913',
        ],
        [
            'nik' => '3217040109800006',
            'name' => 'dr. Olivia Kirana, Sp.OG',
            'gender' => 'female',
            'birthDate' => '1984-06-06',
            'ihs_number' => '10002074224',
        ],
        [
            'nik' => '3519111703800007',
            'name' => 'dr. Alicia Chrissy, Sp.N.',
            'gender' => 'female',
            'birthDate' => '1982-07-07',
            'ihs_number' => '10012572188',
        ],
        [
            'nik' => '5271002009700008',
            'name' => 'dr. Nathalie Tan, Sp.PK.',
            'gender' => 'female',
            'birthDate' => '1981-08-08',
            'ihs_number' => '10018452434',
        ],
        [
            'nik' => '3313096403900009',
            'name' => 'Sheila Annisa S.Kep',
            'gender' => 'female',
            'birthDate' => '1980-09-09',
            'ihs_number' => '10014058550',
        ],
        [
            'nik' => '3578083008700010',
            'name' => 'apt. Aditya Pradhana, S.Farm.',
            'gender' => 'female',
            'birthDate' => '1980-10-10',
            'ihs_number' => '10001915884',
        ],
    ];

    public ?string $base_url = null;

    public ?string $auth_url = null;

    public ?string $fhir_url = null;

    public ?string $client_id = null;

    public ?string $client_secret = null;

    public ?string $organization_id = null;

    public ?string $organization_name = null;

    public $override = null;

    public ?string $satusehat_env = null;

    public array $oauth2_error = [
        'statusCode' => 401,
        'res' => 'Unauthorized. Token not found',
    ];

    private static bool $dotenvLoaded = false;

    private TokenStorageInterface $tokenStorage;

    private LogStorageInterface $logStorage;

    private MemoryTokenStorage $memoryStorage;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        // Safe one-time Dotenv loading (skip disk I/O if already loaded or in Laravel)
        if (! self::$dotenvLoaded) {
            if (! function_exists('config')) {
                $basePath = ss_base_path();
                if (file_exists($basePath . '/.env')) {
                    $dotenv = Dotenv::createImmutable($basePath);
                    $dotenv->safeLoad();
                }
            }
            self::$dotenvLoaded = true;
        }

        $this->override = $config['override'] ?? ss_config('satusehatintegration.ss_parameter_override');

        $this->satusehat_env = $config['environment']
            ?? $config['satusehat_env']
            ?? ($this->override ? null : (getenv('SATUSEHAT_ENV') ?: null));

        if ($this->satusehat_env === 'PROD') {
            $this->base_url = $config['base_url'] ?? (getenv('SATUSEHAT_BASE_URL_PROD') ?: 'https://api-satusehat.kemkes.go.id');
            $this->client_id = $config['client_id'] ?? getenv('CLIENTID_PROD');
            $this->client_secret = $config['client_secret'] ?? getenv('CLIENTSECRET_PROD');
            $this->organization_id = $config['organization_id'] ?? getenv('ORGID_PROD');
        } elseif ($this->satusehat_env === 'STG') {
            $this->base_url = $config['base_url'] ?? (getenv('SATUSEHAT_BASE_URL_STG') ?: 'https://api-satusehat-stg.dto.kemkes.go.id');
            $this->client_id = $config['client_id'] ?? getenv('CLIENTID_STG');
            $this->client_secret = $config['client_secret'] ?? getenv('CLIENTSECRET_STG');
            $this->organization_id = $config['organization_id'] ?? getenv('ORGID_STG');
        } elseif ($this->satusehat_env === 'DEV') {
            $this->base_url = $config['base_url'] ?? (getenv('SATUSEHAT_BASE_URL_DEV') ?: 'https://api-satusehat-dev.dto.kemkes.go.id');
            $this->client_id = $config['client_id'] ?? getenv('CLIENTID_DEV');
            $this->client_secret = $config['client_secret'] ?? getenv('CLIENTSECRET_DEV');
            $this->organization_id = $config['organization_id'] ?? getenv('ORGID_DEV');
        } else {
            // Custom or override fallback
            if (! empty($config['base_url'])) {
                $this->base_url = $config['base_url'];
            }
            if (! empty($config['client_id'])) {
                $this->client_id = $config['client_id'];
            }
            if (! empty($config['client_secret'])) {
                $this->client_secret = $config['client_secret'];
            }
            if (! empty($config['organization_id'])) {
                $this->organization_id = $config['organization_id'];
            }
        }

        $this->organization_name = $config['organization_name']
            ?? (getenv('ORGANIZATION_NAME') ?: (getenv('SATUSEHAT_ORGANIZATION_NAME') ?: (getenv('ORGNAME_' . (string) $this->satusehat_env) ?: null)));

        if (empty($this->satusehat_env) && ! $this->override) {
            throw new OAuth2ClientException('SATUSEHAT environment is missing');
        }

        if (! in_array($this->satusehat_env, ['DEV', 'STG', 'PROD']) && ! $this->override) {
            throw new OAuth2ClientException('SATUSEHAT environment invalid, supported (DEV, STG, PROD). ' . (string) $this->satusehat_env . ' given.');
        }

        if ($this->satusehat_env === 'DEV' && (empty($this->client_id) || empty($this->client_secret) || empty($this->organization_id)) && ! $this->override) {
            throw new OAuth2ClientException('SATUSEHAT environment defined as DEV, but CLIENTID_DEV / CLIENTSECRET_DEV / ORGID_DEV not set');
        }

        if ($this->satusehat_env === 'STG' && (empty($this->client_id) || empty($this->client_secret) || empty($this->organization_id)) && ! $this->override) {
            throw new OAuth2ClientException('SATUSEHAT environment defined as STG, but CLIENTID_STG / CLIENTSECRET_STG / ORGID_STG not set');
        }

        if ($this->satusehat_env === 'PROD' && (empty($this->client_id) || empty($this->client_secret) || empty($this->organization_id)) && ! $this->override) {
            throw new OAuth2ClientException('SATUSEHAT environment defined as PROD, but CLIENTID_PROD / CLIENTSECRET_PROD / ORGID_PROD not set');
        }

        $this->base_url = $this->override ? null : $this->base_url;

        $authEndpoint = getenv('SATUSEHAT_AUTH_ENDPOINT') ?: '/oauth2/v1';
        $fhirEndpoint = getenv('SATUSEHAT_FHIR_ENDPOINT') ?: '/fhir-r4/v1';

        $this->auth_url = $this->base_url . $authEndpoint;
        $this->fhir_url = $this->base_url . $fhirEndpoint;

        // Multi-tier storage
        $this->memoryStorage = new MemoryTokenStorage();
        $this->tokenStorage = $config['token_storage'] ?? StorageFactory::createTokenStorage();
        $this->logStorage = $config['log_storage'] ?? StorageFactory::createLogStorage();
    }

    /**
     * Get OAuth2 Access Token with L1 RAM + L2 Persistent Cache.
     *
     * @return string|array
     */
    public function token()
    {
        $env = (string) $this->satusehat_env;
        $clientId = (string) $this->client_id;

        // Tier 1: Check In-Memory static cache (0ms)
        $memToken = $this->memoryStorage->get($env, $clientId);
        if ($memToken !== null) {
            return $memToken;
        }

        // Tier 2: Check Persistent Storage (File / Cache / DB)
        $storedToken = $this->tokenStorage->get($env, $clientId);
        if ($storedToken !== null) {
            // Warm L1 memory cache
            $this->memoryStorage->set($env, $clientId, $storedToken, 3000);
            return $storedToken;
        }

        // Fallback: Check legacy database if Eloquent model exists
        if (class_exists(\Illuminate\Database\Eloquent\Model::class) && class_exists(\Newus\Satusehat\Models\SatusehatToken::class)) {
            try {
                $dbToken = \Newus\Satusehat\Models\SatusehatToken::where('environment', $this->satusehat_env)
                    ->where('client_id', $this->client_id)
                    ->where('created_at', '>', date('Y-m-d H:i:s', time() - 3000))
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($dbToken && ! empty($dbToken->token)) {
                    $this->memoryStorage->set($env, $clientId, $dbToken->token, 3000);
                    $this->tokenStorage->set($env, $clientId, $dbToken->token, 3000);
                    return $dbToken->token;
                }
            } catch (\Throwable $e) {
                // Ignore database errors if table doesn't exist
            }
        }

        // Tier 3: Fetch new token from SATUSEHAT OAuth2 endpoint
        $client = HttpClientFactory::getClient();
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
        $options = [
            'form_params' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
            ],
        ];

        $url = $this->auth_url . '/accesstoken?grant_type=client_credentials';
        $request = new Request('POST', $url, $headers);

        try {
            $res = $client->sendAsync($request, $options)->wait();
            $contents = json_decode($res->getBody()->getContents());

            if (isset($contents->access_token)) {
                $newToken = (string) $contents->access_token;
                $expiresIn = isset($contents->expires_in) ? (int) $contents->expires_in : 3000;

                // Cache in both tiers
                $this->memoryStorage->set($env, $clientId, $newToken, $expiresIn);
                $this->tokenStorage->set($env, $clientId, $newToken, $expiresIn);

                // Optional legacy database save if Eloquent available
                if (class_exists(\Illuminate\Database\Eloquent\Model::class) && class_exists(\Newus\Satusehat\Models\SatusehatToken::class)) {
                    try {
                        \Newus\Satusehat\Models\SatusehatToken::create([
                            'environment' => $this->satusehat_env,
                            'client_id' => $this->client_id,
                            'token' => $newToken,
                        ]);
                    } catch (\Throwable $e) {
                        // Ignore
                    }
                }

                return $newToken;
            }

            return $this->respondError($this->oauth2_error);
        } catch (ClientException $e) {
            $res = json_decode($e->getResponse()->getBody()->getContents());
            $issueInformation = $res->issue[0]->details->text ?? 'OAuth2 Authentication Failed';

            $this->log($issueInformation, 'POST Token', $url, null, (array) $res);

            return $issueInformation;
        }
    }

    public function log($id, $action, $url, $payload, $response)
    {
        $this->logStorage->log((string) $id, (string) $action, (string) $url, $payload, $response);

        // Optional legacy Eloquent logging if Eloquent exists
        if (class_exists(\Illuminate\Database\Eloquent\Model::class) && class_exists(\Newus\Satusehat\Models\SatusehatLog::class)) {
            try {
                $userId = 'System';
                if (function_exists('auth') && auth()->user()) {
                    $userId = auth()->user()->id;
                }

                $status = new \Newus\Satusehat\Models\SatusehatLog();
                $status->response_id = $id;
                $status->action = $action;
                $status->url = $url;
                $status->payload = $payload;
                $status->response = $response;
                $status->user_id = $userId;
                $status->save();
            } catch (\Throwable $e) {
                // Ignore if DB connection not present
            }
        }
    }

    public function respondError($message)
    {
        $statusCode = $message['statusCode'] ?? 500;
        $res = $message['res'] ?? 'Internal Error';

        return [$statusCode, $res];
    }

    public function get_by_id($resource, $id)
    {
        $accessToken = $this->token();
        if (! isset($accessToken) || is_array($accessToken)) {
            return $this->respondError($this->oauth2_error);
        }

        $client = HttpClientFactory::getClient();
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        $url = $this->fhir_url . '/' . $resource . '/' . $id;
        $request = new Request('GET', $url, $headers);

        try {
            $res = $client->sendAsync($request)->wait();
            $statusCode = $res->getStatusCode();
            $response = json_decode($res->getBody()->getContents());

            $logId = $id;
            if (isset($response->resourceType) && $response->resourceType === 'OperationOutcome') {
                $logId = 'Error ' . $statusCode;
            }
            $this->log($logId, 'GET', $url, null, (array) $response);

            return [$statusCode, $response];
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $res = json_decode($e->getResponse()->getBody()->getContents());

            $this->log('Error ' . $statusCode, 'GET', $url, null, (array) $res);

            return [$statusCode, $res];
        }
    }

    public function get_by_nik($resource, $nik)
    {
        $accessToken = $this->token();
        if (! isset($accessToken) || is_array($accessToken)) {
            return $this->respondError($this->oauth2_error);
        }

        $client = HttpClientFactory::getClient();
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        $url = $this->fhir_url . '/' . $resource . '?identifier=https://fhir.kemkes.go.id/id/nik|' . $nik;
        $request = new Request('GET', $url, $headers);

        try {
            $res = $client->sendAsync($request)->wait();
            $statusCode = $res->getStatusCode();
            $response = json_decode($res->getBody()->getContents());

            if ((isset($response->resourceType) && $response->resourceType === 'OperationOutcome') || (isset($response->total) && $response->total === 0)) {
                $logId = 'Not Found';
            } else {
                $logId = $response->entry[0]->resource->id ?? 'Found';
            }
            $this->log($logId, 'GET', $url, null, (array) $response);

            return [$statusCode, $response];
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $res = json_decode($e->getResponse()->getBody()->getContents());

            $this->log('Error ' . $statusCode, 'GET', $url, null, (array) $res);

            return [$statusCode, $res];
        }
    }

    public function get_by_nik_ibu($resource, $nik_ibu)
    {
        $accessToken = $this->token();
        if (! isset($accessToken) || is_array($accessToken)) {
            return $this->respondError($this->oauth2_error);
        }

        $client = HttpClientFactory::getClient();
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        $url = $this->fhir_url . '/' . $resource . '?identifier=https://fhir.kemkes.go.id/id/nik-ibu|' . $nik_ibu;
        $request = new Request('GET', $url, $headers);

        try {
            $res = $client->sendAsync($request)->wait();
            $statusCode = $res->getStatusCode();
            $response = json_decode($res->getBody()->getContents());

            if ((isset($response->resourceType) && $response->resourceType === 'OperationOutcome') || (isset($response->total) && $response->total === 0)) {
                $logId = 'Not Found';
            } else {
                $logId = $response->entry[0]->resource->id ?? 'Found';
            }
            $this->log($logId, 'GET', $url, null, (array) $response);

            return [$statusCode, $response];
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $res = json_decode($e->getResponse()->getBody()->getContents());

            $this->log('Error ' . $statusCode, 'GET', $url, null, (array) $res);

            return [$statusCode, $res];
        }
    }

    public function ss_kfa_get($resource, $queryString)
    {
        $accessToken = $this->token();
        if (! isset($accessToken) || is_array($accessToken)) {
            return $this->respondError($this->oauth2_error);
        }

        $client = HttpClientFactory::getClient();
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        $url = $this->base_url . '/kfa-v2/' . $resource . $queryString;
        $request = new Request('GET', $url, $headers);

        try {
            $res = $client->sendAsync($request)->wait();
            $statusCode = $res->getStatusCode();
            $response = json_decode($res->getBody()->getContents());

            $logId = 'Kfa_GET_' . $resource;
            $this->log($logId, 'GET', $url, null, (array) $response);

            return [$statusCode, $response];
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $res = json_decode($e->getResponse()->getBody()->getContents());

            $this->log('Error ' . $statusCode, 'GET', $url, null, (array) $res);

            return [$statusCode, $res];
        }
    }

    public function ss_post($resource, $body)
    {
        $accessToken = $this->token();
        if (! isset($accessToken) || is_array($accessToken)) {
            return $this->respondError($this->oauth2_error);
        }

        $client = HttpClientFactory::getClient();
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        $url = $this->fhir_url . ($resource === 'Bundle' ? '' : '/' . $resource);
        $request = new Request('POST', $url, $headers, $body);

        try {
            $res = $client->sendAsync($request)->wait();
            $statusCode = $res->getStatusCode();
            $response = json_decode($res->getBody()->getContents());

            $logId = 'Success ' . $statusCode;
            if (isset($response->id)) {
                $logId = $response->id;
            }
            $this->log($logId, 'POST', $url, (array) json_decode($body), (array) $response);

            return [$statusCode, $response];
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $res = json_decode($e->getResponse()->getBody()->getContents());

            $this->log('Error ' . $statusCode, 'POST', $url, (array) json_decode($body), (array) $res);

            return [$statusCode, $res];
        }
    }

    public function ss_put($resource, $id, $body)
    {
        $accessToken = $this->token();
        if (! isset($accessToken) || is_array($accessToken)) {
            return $this->respondError($this->oauth2_error);
        }

        $client = HttpClientFactory::getClient();
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        $url = $this->fhir_url . '/' . $resource . '/' . $id;
        $request = new Request('PUT', $url, $headers, $body);

        try {
            $res = $client->sendAsync($request)->wait();
            $statusCode = $res->getStatusCode();
            $response = json_decode($res->getBody()->getContents());

            $logId = $response->id ?? ('Success ' . $statusCode);
            $this->log($logId, 'PUT', $url, (array) json_decode($body), (array) $response);

            return [$statusCode, $response];
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $res = json_decode($e->getResponse()->getBody()->getContents());

            $this->log('Error ' . $statusCode, 'PUT', $url, null, (array) $res);

            return [$statusCode, $res];
        }
    }

    /**
     * Dapatkan daftar 10 data pasien dummy sandbox resmi Kemenkes.
     *
     * @return array<int, array<string, string>>
     */
    public function getPatientSandbox(): array
    {
        return $this->patient_sandbox;
    }

    /**
     * Cari data dummy sandbox berdasarkan NIK.
     *
     * @param string $nik
     * @return array<string, string>|null
     */
    public function findPatientSandboxByNik(string $nik): ?array
    {
        foreach ($this->patient_sandbox as $patient) {
            if ($patient['nik'] === $nik) {
                return $patient;
            }
        }

        return null;
    }

    /**
     * Cari data dummy sandbox berdasarkan Nomor IHS.
     *
     * @param string $ihs
     * @return array<string, string>|null
     */
    public function findPatientSandboxByIhs(string $ihs): ?array
    {
        foreach ($this->patient_sandbox as $patient) {
            if ($patient['ihs_number'] === $ihs) {
                return $patient;
            }
        }

        return null;
    }

    /**
     * Dapatkan daftar 10 data dokter / nakes (Practitioner) dummy sandbox resmi Kemenkes.
     *
     * @return array<int, array<string, string>>
     */
    public function getPractitionerSandbox(): array
    {
        return $this->practitioner_sandbox;
    }

    /**
     * Cari data dummy practitioner sandbox berdasarkan NIK.
     *
     * @param string $nik
     * @return array<string, string>|null
     */
    public function findPractitionerSandboxByNik(string $nik): ?array
    {
        foreach ($this->practitioner_sandbox as $practitioner) {
            if ($practitioner['nik'] === $nik) {
                return $practitioner;
            }
        }

        return null;
    }

    /**
     * Cari data dummy practitioner sandbox berdasarkan Nomor IHS.
     *
     * @param string $ihs
     * @return array<string, string>|null
     */
    public function findPractitionerSandboxByIhs(string $ihs): ?array
    {
        foreach ($this->practitioner_sandbox as $practitioner) {
            if ($practitioner['ihs_number'] === $ihs) {
                return $practitioner;
            }
        }

        return null;
    }
}
