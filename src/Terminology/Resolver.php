<?php

declare(strict_types=1);

namespace Newus\Satusehat\Terminology;

use Newus\Satusehat\DataType\CodeableConcept;
use Newus\Satusehat\DataType\Coding;

class Resolver
{
    /**
     * Resolve code or concept string into a CodeableConcept or array of CodeableConcepts.
     *
     * @param $codeOrConcept
     * @return array|CodeableConcept
     */
    public static function resolve($codeOrConcept)
    {
        if ($codeOrConcept instanceof CodeableConcept) {
            return $codeOrConcept;
        }

        if (is_array($codeOrConcept)) {
            return array_map(function ($item) {
                return self::resolve($item);
            }, $codeOrConcept);
        }

        if (is_string($codeOrConcept)) {
            // Check if format is "System:Code" or "Prefix:Code" e.g. "ICD10:A00" or "LOINC:2951-2"
            if (str_contains($codeOrConcept, ':')) {
                [$prefix, $code] = explode(':', $codeOrConcept, 2);
                $upper = strtoupper($prefix);
                switch ($upper) {
                    case 'ICD10':
                        $system = 'http://hl7.org/fhir/sid/icd-10';
                        break;
                    case 'ICD9':
                        $system = 'http://hl7.org/fhir/sid/icd-9-cm';
                        break;
                    case 'LOINC':
                        $system = 'http://loinc.org';
                        break;
                    case 'SNOMED':
                        $system = 'http://snomed.info/sct';
                        break;
                    case 'CVX':
                        $system = 'http://hl7.org/fhir/sid/cvx';
                        break;
                    case 'UCUM':
                        $system = 'http://unitsofmeasure.org';
                        break;
                    default:
                        $system = $prefix;
                        break;
                }

                return (new CodeableConcept())
                    ->addCoding(new Coding($system, trim($code), trim($code)))
                    ->setText(trim($code));
            }

            return (new CodeableConcept())->setText($codeOrConcept);
        }

        return $codeOrConcept;
    }
}
