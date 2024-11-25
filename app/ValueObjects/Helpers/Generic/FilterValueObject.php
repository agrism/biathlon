<?php

namespace App\ValueObjects\Helpers\Generic;

use App\Enums\InputTypeEnum;

class FilterValueObject
{
    public function __construct(
        public InputTypeEnum $inputType,
        public string $key,
        public ?string $title = null,
        public ?string $value = null,
        public array $options = [],
    ) {
    }
}
