<?php

namespace App\ValueObjects\Helpers\Forecasts\FinalDataValueObject;

use App\Enums\Forecast\AwardPointEnum;
use App\Models\User;

class UserValueObject
{
    /**
     * @param PointValueObject[] $points
     * @param AthleteValueObject[] $athletes
     */
    public function __construct(
        public int $id,
        public string $name,
        public array $points = [],
        public array $pointDetails = [],
        public array $athletes = [],
    ) {
    }

    /**
     * @return AthleteValueObject[]
     */
    public function getAthletes(): array
    {
        foreach (range(0,5) as $range){
            if(isset($this->athletes[$range])){
                continue;
            }
            $this->athletes[$range] = new AthleteValueObject(
                id:null,
                tempId: null,
                name: null,
                flagUrl: null,
                stats: null
            );
        }
        return $this->athletes;
    }

    public function getModel(): User
    {
        return User::query()->where('id', $this->id)->first();
    }

    public function getPointsByType(AwardPointEnum $type): float
    {
        foreach ($this->points as $point){
            if($point->type === $type){
                return $point->value;
            }
        }

        return 0;
    }

    public function export(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'points' => collect($this->points)->map(fn(PointValueObject $point) => $point->export())->toArray(),
            'pointDetails' => collect($this->pointDetails)->map(function(array $pointDetailItems){
                return collect($pointDetailItems)->map(function(PointValueObject $pointValueObject){
                    return $pointValueObject->export();
                })->toArray();
            })->toArray(),
            'athletes' => collect($this->athletes)->map(fn(AthleteValueObject $athlete) => $athlete->export())->toArray()
        ];
    }
}
