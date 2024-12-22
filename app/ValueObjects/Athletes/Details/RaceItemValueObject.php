<?php

namespace App\ValueObjects\Athletes\Details;

class RaceItemValueObject
{
    public function __construct(
        public ?string $RaceId = null,
        public ?string $SeasonId = null,
        public ?string $Season = null,
        public ?string $Comp = null,
        public ?string $Competition = null,
        public ?string $Level = null,
        public ?string $Place = null,
        public ?string $PlaceNat = null,
        public ?string $Rank = null,
        public ?string $SO = null,
        public ?string $Pen = null,
        public ?string $Shootings = null,
    ) {
    }

    public function export(): array
    {
        return [
            'RaceId' => $this->RaceId,
            'SeasonId' => $this->SeasonId,
            'Season' => $this->Season,
            'Comp' => $this->Comp,
            'Competition' => $this->Competition,
            'Level' => $this->Level,
            'Place' => $this->Place,
            'PlaceNat' => $this->PlaceNat,
            'Rank' => $this->Rank,
            'SO' => $this->SO,
            'Pen' => $this->Pen,
            'Shootings' => $this->Shootings,
        ];
    }
}
