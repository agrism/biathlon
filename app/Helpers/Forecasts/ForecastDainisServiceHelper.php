<?php

namespace App\Helpers\Forecasts;

use App\Enums\DisciplineEnum;
use App\Enums\Forecast\AwardPointEnum;
use App\Helpers\InstanceTrait;
use App\Models\EventCompetition;
use App\Models\Forecast;
use App\Models\User;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\PointValueObject;

class ForecastDainisServiceHelper extends ForecastAbstractionHelper
{
    use InstanceTrait;

    protected EventCompetition $competition;
    protected bool $isTeamDiscipline = false;
    protected PointCalculatorServiceHelper $pointCalculator;

    protected float $mainPoints = 0;
    protected float $bonusPoints = 0;

    protected array $pointDetails = [];
    protected array $resultAthleteIds = [];

    protected array $userGivenForecastAthleteIds = [];

    protected const POINT_DETAILS_REGULAR_INDEX = 0;
    protected const POINT_DETAILS_BONUS_INDEX = 1;

    protected array $matrix = [
        'regular' => [
            'individual' => [
                0 => 21,
                1 => 15,
                2 => 12,
                3 => 9,
                4 => 6,
                5 => 3,
            ],
            'team' => [
                0 => 7,
                1 => 5,
                2 => 4,
                3 => 3,
                4 => 2,
                5 => 1,
            ]
        ],
        'bonus' => [
            'individual' => [
                'gold' => 21,
                'silver' => 15,
                'bronze' => 9,
            ],
            'team' => [
                'gold' => 7,
                'silver' => 5,
                'bronze' => 3,
            ],
        ]

    ];

    public function calculateUserPoints(Forecast $forecast, User $user): self
    {
        $this->mainPoints = 0;
        $this->bonusPoints = 0;

        foreach (range(0,5) as $_){
            $this->pointDetails[] = [
                self::POINT_DETAILS_REGULAR_INDEX => new PointValueObject(
                    type: AwardPointEnum::REGULAR_POINT,
                    value: 0,
                ),
                self::POINT_DETAILS_BONUS_INDEX => new PointValueObject(
                    type: AwardPointEnum::BONUS_POINT,
                    value: 0,
                ),
            ];
        }

        $this->competition = $forecast->competition;

        $this->isTeamDiscipline = DisciplineEnum::tryFrom($this->competition->discipline_remote_id)->isTeamDiscipline();

        $this->resultAthleteIds = collect($forecast->final_data->results)->pluck('tempId')->toArray();

        $this->userGivenForecastAthleteIds = collect($forecast->final_data->getUserByUserModel($user)->athletes)->pluck('tempId')->toArray();

        $this->registerMainPoints();
        $this->registerBonusPoints();

        return $this;
    }

    public function overrideMatrix(array $data): self
    {
        foreach([
            'regular.individual.0',
            'regular.individual.1',
            'regular.individual.2',
            'regular.individual.3',
            'regular.individual.4',
            'regular.individual.5',

            'regular.team.0',
            'regular.team.1',
            'regular.team.2',
            'regular.team.3',
            'regular.team.4',
            'regular.team.5',

            'bonus.individual.gold',
            'bonus.individual.silver',
            'bonus.individual.bronze',

            'bonus.team.gold',
            'bonus.team.silver',
            'bonus.team.bronze',
                ] as $key){
            $default = data_get($this->matrix, $key);
            data_set($this->matrix, $key, data_get($data, $key, $default));
        }



        return $this;
    }

    public function getMainPoints(): float
    {
        return $this->mainPoints;
    }

    public function getBonusPoints(): float
    {
        return $this->bonusPoints;
    }

    public function getPointDetails(): array
    {
        return $this->pointDetails;
    }

    protected function registerMainPoints(): self
    {
        $this->mainPoints = $this->getPrecisionPoints();
        return $this;
    }

    protected function registerBonusPoints(): self
    {
        $this->bonusPoints = 0;

        if($this->foundGoldPlace()){
            $this->bonusPoints += $points = match ($this->isTeamDiscipline){
                false => data_get($this->matrix, 'bonus.individual.gold'),
                true => data_get($this->matrix, 'bonus.team.gold'),
            };

            data_set(
                target: $this->pointDetails,
                key: '0.' . self::POINT_DETAILS_BONUS_INDEX,
                value:  new PointValueObject(type: AwardPointEnum::BONUS_POINT,value: $points,),
            );
        }

        if($this->foundSilverPlace()){
            $this->bonusPoints += $points = match ($this->isTeamDiscipline){
                false => data_get($this->matrix, 'bonus.individual.silver'),
                true => data_get($this->matrix, 'bonus.team.silver'),
            };

            data_set(
                target: $this->pointDetails,
                key: '1.' . self::POINT_DETAILS_BONUS_INDEX,
                value:  new PointValueObject(type: AwardPointEnum::BONUS_POINT,value: $points,),
            );
        }

        if($this->foundBronzePlace()){
            $this->bonusPoints += $points = match ($this->isTeamDiscipline){
                false => data_get($this->matrix, 'bonus.individual.bronze'),
                true => data_get($this->matrix, 'bonus.team.bronze'),
            };

            data_set(
                target: $this->pointDetails,
                key: '2.' . self::POINT_DETAILS_BONUS_INDEX,
                value:  new PointValueObject(type: AwardPointEnum::BONUS_POINT,value: $points,),
            );
        }

        return $this;
    }

    private function getPrecisionPoints(): int
    {
        $precisionPoints = 0;

        $index = -1;

        foreach ($this->userGivenForecastAthleteIds as $userSelectedPlace => $userGivenForecastAthleteId){

            $index++;

            $result = array_keys($this->resultAthleteIds, $userGivenForecastAthleteId);

            if(empty($result)){
                continue;
            }

            $athleteResultPlace = array_shift($result);

            $precisionPoints += $points =  $this->precisionDeltaPoints(
                $this->getPrecisionDelta(
                    userSelectedAthletePlace: $userSelectedPlace,
                    resultAthletePlace: $athleteResultPlace
                )
            );

            data_set(
                target: $this->pointDetails,
                key: $index .'.' . self::POINT_DETAILS_REGULAR_INDEX,
                value:  new PointValueObject(type: AwardPointEnum::REGULAR_POINT,value: $points,),
            );
        }

        return $precisionPoints;
    }

    private function getPrecisionDelta(int $userSelectedAthletePlace, int $resultAthletePlace): int
    {
        return abs($resultAthletePlace - $userSelectedAthletePlace);
    }

    private function precisionDeltaPoints(int $precisionDelta): int
    {
        if($this->isTeamDiscipline){
            return match ($precisionDelta){
                0 => data_get($this->matrix, 'regular.team.0'),
                1 => data_get($this->matrix, 'regular.team.1'),
                2 => data_get($this->matrix, 'regular.team.2'),
                3 => data_get($this->matrix, 'regular.team.3'),
                4 => data_get($this->matrix, 'regular.team.4'),
                5 => data_get($this->matrix, 'regular.team.5'),
                default => 0
            };
        }

        return match ($precisionDelta){
            0 => data_get($this->matrix, 'regular.individual.0'),
            1 => data_get($this->matrix, 'regular.individual.1'),
            2 => data_get($this->matrix, 'regular.individual.2'),
            3 => data_get($this->matrix, 'regular.individual.3'),
            4 => data_get($this->matrix, 'regular.individual.4'),
            5 => data_get($this->matrix, 'regular.individual.5'),
            default => 0
        };
    }

    private function foundGoldPlace(): bool
    {
        return ($this->resultAthleteIds[0] ?? 'x') == ($this->userGivenForecastAthleteIds[0] ?? 'y');
    }

    private function foundSilverPlace(): bool
    {
        return ($this->resultAthleteIds[1] ?? 'x') == ($this->userGivenForecastAthleteIds[1] ?? 'y');
    }

    private function foundBronzePlace(): bool
    {
        return ($this->resultAthleteIds[2] ?? 'x') == ($this->userGivenForecastAthleteIds[2] ?? 'y');
    }

    public function getMatrix(): array
    {
        return $this->matrix;
    }
}
