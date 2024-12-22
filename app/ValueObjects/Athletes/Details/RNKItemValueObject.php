<?php

namespace App\ValueObjects\Athletes\Details;

class RNKItemValueObject
{
    public function __construct(
        public ?string $Description = null,
        public ?int $Individual = null,
        public ?int $Sprint = null,
        public ?int $Pursuit = null,
        public ?int $MassStart = null,
        public ?int $IndividualTotal = null,
        public ?int $Team = null,
        public ?int $Relay = null,
        public ?int $MxRelay = null,
        public ?int $SingleMxRelay = null,
        public ?int $Total = null,
        public ?int $Total_WC = null,
        public ?int $Total_WCH = null,
        public ?int $Total_OWG = null,
        public ?int $All_WC = null,
        public ?int $All_WCH = null,
        public ?int $All_OWG = null,
    ) {
    }

    public function export(): array
    {
        return [
            'Description' => $this->Description,
            'Individual' => $this->Individual,
            'Sprint' => $this->Sprint,
            'Pursuit' => $this->Pursuit,
            'MassStart' => $this->MassStart,
            'IndividualTotal' => $this->IndividualTotal,
            'Team' => $this->Team,
            'Relay' => $this->Relay,
            'MxRelay' => $this->MxRelay,
            'SingleMxRelay' => $this->SingleMxRelay,
            'Total' => $this->Total,
            'Total_WC' => $this->Total_WC,
            'Total_WCH' => $this->Total_WCH,
            'Total_OWG' => $this->Total_OWG,
            'All_WC' => $this->All_WC,
            'All_WCH' => $this->All_WCH,
            'All_OWG' => $this->All_OWG,
        ];
    }
}
