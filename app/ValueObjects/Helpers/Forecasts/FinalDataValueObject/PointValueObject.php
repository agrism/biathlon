<?php

namespace App\ValueObjects\Helpers\Forecasts\FinalDataValueObject;

use App\Enums\Forecast\AwardPointEnum;

class PointValueObject
{
    public function __construct(
        public AwardPointEnum $type,
        public float $value,
    )
    {
    }

    public function export(): array
    {
        return [
            'type' => $this->type->value,
            'value' => $this->value
        ];
    }
}
