<?php

namespace App\ValueObjects\Athletes;

use App\ValueObjects\Athletes\Details\BadgeItemValueObject;
use App\ValueObjects\Athletes\Details\BibItemValueObject;
use App\ValueObjects\Athletes\Details\ItemValueObject;
use App\ValueObjects\Athletes\Details\OwgItemValueObject;
use App\ValueObjects\Athletes\Details\RaceItemValueObject;
use App\ValueObjects\Athletes\Details\RNKItemValueObject;
use Carbon\Carbon;

class AthleteDetailsValueObject
{
    /**
     * @param BibItemValueObject[] $Bibs
     * @param ItemValueObject[] $Personal
     * @param ItemValueObject[] $Sport
     * @param ItemValueObject[] $Equipment
     * @param ItemValueObject[] $Stats
     * @param ItemValueObject[] $Stories
     * @param RaceItemValueObject[] $Recent
     * @param OwgItemValueObject[] $OWG
     * @param OwgItemValueObject[] $WCH
     * @param OwgItemValueObject[] $JWCH
     * @param OwgItemValueObject[] $WC
     * @param OwgItemValueObject[] $IC
     * @param OwgItemValueObject[] $JC
     * @param array<string, null|int> $Podiums
     * @param RaceItemValueObject[] $TopResults
     * @param RNKItemValueObject[] $RNKS
     * @param BadgeItemValueObject[] $Badges
     *
     */
    public function __construct(
        public ?string $IBUId = null,
        public ?string $FullName = null,
        public ?string $FamilyName = null,
        public ?string $GivenName = null,
        public ?string $otherFamilyNames = null,
        public ?string $otherGivenNames = null,
        public ?string $NAT = null,
        public ?string $NF = null,
        public ?string $Birthdate = null,
        public ?int $BirthYear = null,
        public ?int $Age = null,
        public ?string $GenderId = null,
        public ?string $Functions = null,
        public ?string $PhotoURI = null,
        public ?string $FlagURI = null,
        public array $Bibs = [],
        public array $Personal = [],
        public array $Sport = [],
        public array $Equipment = [],
        public array $Stats = [],
        public array $Stories = [],
        public array $Recent = [],
        public array $OWG = [],
        public array $WCH = [],
        public array $JWCH = [],
        public array $WC = [],
        public array $IC = [],
        public array $JC = [],
        public array $Podiums = [],
        public ?string $CompetitionRankings = null,
        public ?string $IBUCupScores = null,
        public array $TopResults = [],
        public array $StatSeasons = [],
        public array $StatShooting = [],
        public array $StatShootingProne = [],
        public array $StatShootingStanding = [],
        public array $StatSkiing = [],
        public array $StatSkiKMB = [],
        public array $StatStarts = [],
        public array $StatLevel = [],
        public array $RNKS = [],
        public array $Badges = [],
    ) {
    }

    public function export(): array
    {
        return [
            'IBUId' => $this->IBUId,
            'FullName' => $this->FullName,
            'FamilyName' => $this->FamilyName,
            'GivenName' => $this->GivenName,
            'otherFamilyNames' => $this->otherFamilyNames,
            'otherGivenNames' => $this->otherGivenNames,
            'NAT' => $this->NAT,
            'NF' => $this->NF,
            'Birthdate' => $this->Birthdate,
            'BirthYear' => $this->BirthYear,
            'Age' => $this->Age,
            'GenderId' => $this->GenderId,
            'Functions' => $this->Functions,
            'PhotoURI' => $this->PhotoURI,
            'FlagURI' => $this->FlagURI,
            'Bibs' => collect($this->Bibs)->map(fn(BibItemValueObject $i) => $i->export())->toArray(),
            'Personal' => collect($this->Personal)->map(fn(ItemValueObject $i) => $i->export())->toArray(),
            'Sport' => collect($this->Sport)->map(fn(ItemValueObject $i) => $i->export())->toArray(),
            'Equipment' => collect($this->Equipment)->map(fn(ItemValueObject $i) => $i->export())->toArray(),
            'Stats' => collect($this->Stats)->map(fn(ItemValueObject $i) => $i->export())->toArray(),
            'Stories' => collect($this->Stories)->map(fn(ItemValueObject $i) => $i->export())->toArray(),
            'Recent' => collect($this->Recent)->map(fn(RaceItemValueObject $i) => $i->export())->toArray(),
            'OWG' => collect($this->OWG)->map(fn(OwgItemValueObject $i) => $i->export())->toArray(),
            'WCH' => collect($this->WCH)->map(fn(OwgItemValueObject $i) => $i->export())->toArray(),
            'JWCH' => collect($this->JWCH)->map(fn(OwgItemValueObject $i) => $i->export())->toArray(),
            'WC' => collect($this->WC)->map(fn(OwgItemValueObject $i) => $i->export())->toArray(),
            'IC' => collect($this->IC)->map(fn(OwgItemValueObject $i) => $i->export())->toArray(),
            'JC' => collect($this->JC)->map(fn(OwgItemValueObject $i) => $i->export())->toArray(),
            'Podiums' => $this->Podiums,
            'CompetitionRankings' => $this->CompetitionRankings,
            'IBUCupScores' => $this->IBUCupScores,
            'TopResults' => collect($this->TopResults)->map(fn(RaceItemValueObject $i) => $i->export())->toArray(),
            'StatSeasons' => $this->StatSeasons,
            'StatShooting' => $this->StatShooting,
            'StatShootingProne' => $this->StatShootingProne,
            'StatShootingStanding' => $this->StatShootingStanding,
            'StatSkiing' => $this->StatSkiing,
            'StatSkiKMB' => $this->StatSkiKMB,
            'StatStarts' => $this->StatStarts,
            'StatLevel' => $this->StatStarts,
            'RNKS' => collect($this->RNKS)->map(fn(RNKItemValueObject $i) => $i->export())->toArray(),
            'Badges' => collect($this->Badges)->map(fn(BadgeItemValueObject $i) => $i->export())->toArray(),
        ];
    }
}
