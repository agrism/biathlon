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

    /**
     * Get regular, bonus, and total points earned for a predicted athlete at position $index (0 to 5).
     *
     * @param int $index Athlete position index (0 to 5)
     * @param AthleteValueObject[]|null $results Top official results for fallback calculation
     * @param bool $isTeam Whether the race is a team discipline
     * @return array{regular: float, bonus: float, total: float}
     */
    public function getPointsForAthlete(int $index, ?array $results = null, bool $isTeam = false): array
    {
        $regular = 0.0;
        $bonus = 0.0;

        if (!empty($this->pointDetails[$index]) && is_array($this->pointDetails[$index])) {
            foreach ($this->pointDetails[$index] as $point) {
                if ($point instanceof PointValueObject) {
                    if ($point->type === AwardPointEnum::REGULAR_POINT) {
                        $regular += (float) $point->value;
                    } elseif ($point->type === AwardPointEnum::BONUS_POINT) {
                        $bonus += (float) $point->value;
                    }
                } elseif (is_array($point)) {
                    $type = data_get($point, 'type');
                    $val = (float) data_get($point, 'value', 0);
                    if ($type === AwardPointEnum::REGULAR_POINT->value || $type === AwardPointEnum::REGULAR_POINT) {
                        $regular += $val;
                    } elseif ($type === AwardPointEnum::BONUS_POINT->value || $type === AwardPointEnum::BONUS_POINT) {
                        $bonus += $val;
                    }
                }
            }

            return [
                'regular' => $regular,
                'bonus' => $bonus,
                'total' => $regular + $bonus,
            ];
        }

        // Fallback for cases where pointDetails is not stored in JSON
        if (!empty($results) && isset($this->athletes[$index])) {
            $predictedTempId = $this->athletes[$index]->tempId;
            if ($predictedTempId) {
                $resultTempIds = collect($results)->pluck('tempId')->toArray();
                $foundKey = array_search($predictedTempId, $resultTempIds, true);
                if ($foundKey !== false) {
                    $delta = abs($foundKey - $index);
                    $regular = match ($delta) {
                        0 => $isTeam ? 7.0 : 21.0,
                        1 => $isTeam ? 5.0 : 15.0,
                        2 => $isTeam ? 4.0 : 12.0,
                        3 => $isTeam ? 3.0 : 9.0,
                        4 => $isTeam ? 2.0 : 6.0,
                        5 => $isTeam ? 1.0 : 3.0,
                        default => 0.0,
                    };

                    if ($index === 0 && $foundKey === 0) {
                        $bonus = $isTeam ? 7.0 : 21.0;
                    } elseif ($index === 1 && $foundKey === 1) {
                        $bonus = $isTeam ? 5.0 : 15.0;
                    } elseif ($index === 2 && $foundKey === 2) {
                        $bonus = $isTeam ? 3.0 : 9.0;
                    }
                }
            }
        }

        return [
            'regular' => $regular,
            'bonus' => $bonus,
            'total' => $regular + $bonus,
        ];
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
