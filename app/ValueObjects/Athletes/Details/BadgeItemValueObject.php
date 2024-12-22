<?php

namespace App\ValueObjects\Athletes\Details;

class BadgeItemValueObject
{
    public function __construct(
        public ?string $Code = null,
        public ?string $Description = null,
        public ?string $Value = null,
    ) {
    }

    public function export(): array
    {
        return [
            'Code' => $this->Code,
            'Description' => $this->Description,
            'Value' => $this->Value,
        ];
    }
}
