<?php

namespace App\ValueObjects\Helpers\Forecasts\FinalDataValueObject;

use App\Models\Athlete;
use App\ValueObjects\Athletes\AthleteStatsDetailValueObject;

class AthleteValueObject
{
    public function __construct(
        public ?string $id,
        public ?string $tempId,
        public ?string $name,
        public ?string $flagUrl,
        public ?AthleteStatsDetailValueObject $stats = null,
        public ?bool $isHidden = false,
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
            'stats' => $this->stats?->export(),
            'isHidden' => $this->isHidden,
        ];
    }

    public function getModel(): ?Athlete
    {
        return Athlete::query()->where('id', $this->id)->first();
    }
}
