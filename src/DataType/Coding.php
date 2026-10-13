<?php

declare(strict_types=1);

namespace Newus\Satusehat\DataType;

/**
 * @property $system
 * @property $version
 * @property $code
 * @property $display
 * @property $userSelected
 */
class Coding extends DataType
{
    public ?string $system = null;
    public ?string $version = null;
    public ?string $code = null;
    public ?string $display = null;
    public ?bool $userSelected = null;

    public function __construct(
        ?string $system = null,
        ?string $code = null,
        ?string $display = null,
        ?string $version = null,
        ?bool $userSelected = null
    ) {
        $this->system = $system;
        $this->code = $code;
        $this->display = $display;
        $this->version = $version;
        $this->userSelected = $userSelected;
    }
}
