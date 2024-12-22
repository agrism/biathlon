<?php

namespace App\ValueObjects\Athletes\Details;

class OwgItemValueObject
{
    public function __construct(
        public ?string $Year = null,
        public ?string $SeasonId = null,
        public ?string $Place = null,
        public ?string $Ind = null,
        public ?string $Spr = null,
        public ?string $Pur = null,
        public ?string $Mas = null,
        public ?string $Rel = null,
        public ?string $MxRel = null,
        public ?string $SxRel = null,
        public ?string $Tot_Id = null,
        public ?string $Ind_Id = null,
        public ?string $Spr_Id = null,
        public ?string $Pur_Id = null,
        public ?string $Mas_Id = null,
        public ?string $Rel_Id = null,
        public ?string $MxRel_Id = null,
        public ?string $SxRel_Id = null,
        public ?string $Tot = null,
        public ?string $Tot_Score = null,
    ) {
    }

    public function export(): array
    {
        return [
            'Year' => $this->Year,
            'SeasonId' => $this->SeasonId,
            'Place' => $this->Place,
            'Ind' => $this->Ind,
            'Spr' => $this->Spr,
            'Pur' => $this->Pur,
            'Mas' => $this->Mas,
            'Rel' => $this->Rel,
            'MxRel' => $this->MxRel,
            'SxRel' => $this->SxRel,
            'Tot_Id' => $this->Tot_Id,
            'Ind_Id' => $this->Ind_Id,
            'Spr_Id' => $this->Spr_Id,
            'Pur_Id' => $this->Pur_Id,
            'Mas_Id' => $this->Mas_Id,
            'Rel_Id' => $this->Rel_Id,
            'MxRel_Id' => $this->MxRel_Id,
            'SxRel_Id' => $this->SxRel_Id,
            'Tot' => $this->Tot,
            'Tot_Score' => $this->Tot_Score,
        ];
    }
}
