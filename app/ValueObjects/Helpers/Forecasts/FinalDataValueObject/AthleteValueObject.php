<?php

namespace App\ValueObjects\Helpers\Forecasts\FinalDataValueObject;

use App\Models\Athlete;
use App\ValueObjects\Athletes\AthleteStatsDetailValueObject;
use Illuminate\Support\Str;

class AthleteValueObject
{
    public function __construct(
        public ?string $id,
        public ?string $tempId,
        public ?string $name,
        public ?string $flagUrl,
        public ?AthleteStatsDetailValueObject $stats = null,
        public ?bool $isHidden = false,
        public ?bool $isInStartList = false,
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
            'isInStartList' => $this->isInStartList,
        ];
    }

    public function getShortName(): ?string
    {
        $exploded = explode(' ', $this->name);
        if(count($exploded) < 2){
            return $this->name;
        }
        $lastName = array_pop($exploded);
        $firstName = array_pop($exploded);

        $length = 1;

        if(in_array($lastName, ['BOE', 'CLAUDE'])){
            $length = 2;
        }

        return Str::of($firstName)->substr(0, $length)->ucfirst()->toString() . '.' . $lastName;
    }

    public function getModel(): ?Athlete
    {
        return Athlete::query()->where('id', $this->id)->first();
    }
}
