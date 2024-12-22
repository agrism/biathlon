<?php

namespace App\ValueObjects\Athletes\Details;

class ItemValueObject
{
    public function __construct(
        public ?string $id = null,
        public ?string $Description = null,
        public ?string $Value = null,
    ) {
    }

    public function export(): array
    {
        return [
            'id' => $this->id,
            'Description' => $this->Description,
            'Value' => $this->Value,
        ];
    }
}
