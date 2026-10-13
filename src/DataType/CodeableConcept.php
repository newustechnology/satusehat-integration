<?php

declare(strict_types=1);

namespace Newus\Satusehat\DataType;

/**
 * @property $coding
 * @property $text
 */
class CodeableConcept extends DataType
{
    /** @var Coding[] */
    public array $coding = [];
    public ?string $text = null;

    public function __construct(?string $coding = null, ?string $text = null)
    {
        $this->text = $text;
    }

    public function addCoding(Coding $coding): self
    {
        $this->coding[] = $coding;
        return $this;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;
        return $this;
    }
}
