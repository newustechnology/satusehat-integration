<?php

declare(strict_types=1);

namespace Newus\Satusehat\Exception\Helper;

use Exception;

class SSRMEException extends Exception
{
    public static function missingField(string $field): self
    {
        return new self("SSRME payload missing required field: [{$field}]");
    }

    public static function invalidAccessToken(string $detail = ''): self
    {
        $message = 'Invalid Access Token: Token autentikasi SATUSEHAT kedaluwarsa atau tidak valid.';
        if ($detail !== '') {
            $message .= " Detail: {$detail}";
        }
        return new self($message, 401);
    }

    public static function wrongOrganization(string $orgId, string $detail = ''): self
    {
        $message = "Wrong Organization: organization_id [{$orgId}] tidak sesuai dengan credential fasyankes di DFO/RS Online.";
        if ($detail !== '') {
            $message .= " Detail: {$detail}";
        }
        return new self($message, 400);
    }

    public static function patientNotFound(string $patientId, string $detail = ''): self
    {
        $message = "Patient Not Found: Pasien dengan ID [{$patientId}] tidak ditemukan di SATUSEHAT. Pastikan pasien sudah melalui proses KYC/validasi NIK.";
        if ($detail !== '') {
            $message .= " Detail: {$detail}";
        }
        return new self($message, 400);
    }

    public static function practitionerNotFound(string $practitionerId, string $detail = ''): self
    {
        $message = "Practitioner Not Found: Nakes dengan ID [{$practitionerId}] tidak ditemukan. Pastikan dokter/nakes terdaftar di SISDMK dan memiliki SIP aktif.";
        if ($detail !== '') {
            $message .= " Detail: {$detail}";
        }
        return new self($message, 400);
    }
}
