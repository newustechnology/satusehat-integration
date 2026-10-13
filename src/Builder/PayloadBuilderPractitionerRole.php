<?php

declare(strict_types=1);

namespace Newus\Satusehat\Builder;

use Newus\Satusehat\DataType\CodeableConcept;
use Newus\Satusehat\DataType\ContactPoint;
use Newus\Satusehat\DataType\Identifier;
use Newus\Satusehat\DataType\Period;
use Newus\Satusehat\DataType\Reference;

/**
 * PractitionerRole FHIR R4 Resource Builder
 * @link https://www.hl7.org/fhir/practitionerrole.html
 */
class PayloadBuilderPractitionerRole extends Builder
{
    protected string $resourceType = 'PractitionerRole';

    public function __construct()
    {
        $this->data['resourceType'] = $this->resourceType;
    }

    public function setId(string $id): self
    {
        $this->set('id', $id);
        return $this;
    }

    public function addIdentifier(Identifier $identifier): self
    {
        $this->push('identifier', $identifier->toArray());
        return $this;
    }

    public function setActive(bool $active): self
    {
        $this->set('active', $active);
        return $this;
    }

    public function setPractitioner(Reference $practitioner): self
    {
        $this->set('practitioner', $practitioner->toArray());
        return $this;
    }

    public function setOrganization(Reference $organization): self
    {
        $this->set('organization', $organization->toArray());
        return $this;
    }

    public function addCode(CodeableConcept $code): self
    {
        $this->push('code', $code->toArray());
        return $this;
    }

    public function addSpecialty(CodeableConcept $specialty): self
    {
        $this->push('specialty', $specialty->toArray());
        return $this;
    }

    public function addLocation(Reference $location): self
    {
        $this->push('location', $location->toArray());
        return $this;
    }

    public function addHealthcareService(Reference $healthcareService): self
    {
        $this->push('healthcareService', $healthcareService->toArray());
        return $this;
    }

    public function addTelecom(ContactPoint $telecom): self
    {
        $this->push('telecom', $telecom->toArray());
        return $this;
    }

    public function addAvailableTime(
        array $daysOfWeek,
        ?string $availableStartTime = null,
        ?string $availableEndTime = null,
        ?string $description = null
    ): self {
        $availableTime = [];

        if (!empty($daysOfWeek)) {
            $availableTime['daysOfWeek'] = $daysOfWeek;
        }

        if ($availableStartTime !== null) {
            $availableTime['availableStartTime'] = $availableStartTime;
        }

        if ($availableEndTime !== null) {
            $availableTime['availableEndTime'] = $availableEndTime;
        }

        if ($description !== null) {
            $availableTime['description'] = $description;
        }

        $this->push('availableTime', $availableTime);
        return $this;
    }

    public function addNotAvailable(
        string $description,
        ?Period $during = null
    ): self {
        $notAvailable = ['description' => $description];

        if ($during !== null) {
            $notAvailable['during'] = $during->toArray();
        }

        $this->push('notAvailable', $notAvailable);
        return $this;
    }

    public function addEndpoint(Reference $endpoint): self
    {
        $this->push('endpoint', $endpoint->toArray());
        return $this;
    }

    public function addAvailabilityExceptions(string $availabilityExceptions): self
    {
        $this->set('availabilityExceptions', $availabilityExceptions);
        return $this;
    }

    public function addExtension(string $url, $value, ?string $valueType = null): self
    {
        $extension = ['url' => $url];

        if ($valueType !== null) {
            $extension['value' . ucfirst($valueType)] = $value;
        } else {
            $extension['valueString'] = is_string($value) ? $value : $value;
        }

        $this->push('extension', $extension);
        return $this;
    }

    public function build(): array
    {
        return parent::build();
    }
}
