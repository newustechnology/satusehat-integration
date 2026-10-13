<?php

declare(strict_types=1);

namespace Newus\Satusehat\DataType;

/**
 * @property $contentType
 * @property $language
 * @property $data
 * @property $url
 * @property $size
 * @property $hash
 * @property $title
 * @property $creation
 */
class Attachment extends DataType
{
    public ?string $contentType = null;
    public ?string $language = null;
    public ?string $data = null;
    public ?string $url = null;
    public ?int $size = null;
    public ?string $hash = null;
    public ?string $title = null;
    public ?string $creation = null;

    public function __construct(
        ?string $contentType = null,
        ?string $data = null,
        ?string $url = null
    ) {
        $this->contentType = $contentType;
        $this->data = $data;
        $this->url = $url;
    }
}
