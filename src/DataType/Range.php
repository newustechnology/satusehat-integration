<?php

declare(strict_types=1);

namespace Newus\Satusehat\DataType;

/**
 * @property $low
 * @property $high
 */
class Range extends DataType
{
    public ?Quantity $low = null;
    public ?Quantity $high = null;

    public function __construct(?Quantity $low = null, ?Quantity $high = null)
    {
        $this->low = $low;
        $this->high = $high;
    }
}
