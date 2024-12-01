<?php

namespace App\ValueObjects\Helpers\Forecasts\FinalDataValueObject;

use App\Models\Athlete;

class AthleteValueObject
{
    public function __construct(
        public ?string $id,
        public ?string $tempId,
        public ?string $name,
        public ?string $flagUrl,
    )
    {
    }

    public function export(): array
    {
        return [
            'id' => $this->id,
            'tempId' => $this->tempId,
            'name' => $this->name,
            'flagUrl' => $this->flagUrl,
        ];
    }

    public function getModel(): ?Athlete
    {
        return Athlete::query()->where('id', $this->id)->first();
    }
}
