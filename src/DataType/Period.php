<?php

declare(strict_types=1);

namespace Newus\Satusehat\DataType;

/**
 * @property $start
 * @property $end
 */
class Period extends DataType
{
    public ?string $start = null;
    public ?string $end = null;

    public function __construct(?string $start = null, ?string $end = null)
    {
        $this->start = $start;
        $this->end = $end;
    }
}
