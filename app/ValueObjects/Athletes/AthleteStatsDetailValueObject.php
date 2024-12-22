<?php

namespace App\ValueObjects\Athletes;

class AthleteStatsDetailValueObject
{
    public function __construct(
        public ?string $statSeason = null,
        public ?float $statsSeasonPointTotal = null,
        public ?float $statsSeasonPointsSprint = null,
        public ?float $statsSeasonPointsIndividual = null,
        public ?float $statsSeasonPointsPursuit = null,
        public ?float $statsSeasonPointsMass = null,
        public ?float $statsSkiKmb = null,
        public ?float $statSkiing = null,
        public ?float $statShooting = null,
        public ?float $statShootingProne = null,
        public ?float $statShootingStanding = null,
    ) {
    }

    public function export(): array
    {
        return [
            'statSeason' => $this->statSeason,
            'statsSeasonPointTotal' => $this->statsSeasonPointTotal,
            'statsSeasonPointsSprint' => $this->statsSeasonPointsSprint,
            'statsSeasonPointsIndividual' => $this->statsSeasonPointsIndividual,
            'statsSeasonPointsPursuit' => $this->statsSeasonPointsPursuit,
            'statsSeasonPointsMass' => $this->statsSeasonPointsMass,
            'statsSkiKmb' => $this->statsSkiKmb,
            'statSkiing' => $this->statSkiing,
            'statShooting' => $this->statShooting,
            'statShootingProne' => $this->statShootingProne,
            'statShootingStanding' => $this->statShootingStanding,
        ];
    }
}
