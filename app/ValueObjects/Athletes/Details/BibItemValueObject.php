<?php

namespace App\ValueObjects\Athletes\Details;

class BibItemValueObject
{
    public function __construct(
        public ?string $Code = null,
        public ?string $Color = null,
        public ?string $Description = null,
    ) {
    }

    public function export(): array
    {
        return [
            'Code' => $this->Code,
            'Color' => $this->Color,
            'Description' => $this->Description,
        ];
    }
}
