<?php

declare(strict_types=1);

namespace Newus\Satusehat\DataType;

/**
 * @property $value
 * @property $comparator
 * @property $unit
 * @property $system
 * @property $code
 */
class SimpleQuantity extends Quantity
{
    public function __construct(
        ?float $value = null,
        ?string $unit = null,
        ?string $system = null,
        ?string $code = null
    ) {
        $this->value = $value;
        $this->unit = $unit;
        $this->system = $system;
        $this->code = $code;
    }
}
