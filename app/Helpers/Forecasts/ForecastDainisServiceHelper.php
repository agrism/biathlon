<?php

namespace App\Helpers\Forecasts;

use App\Enums\DisciplineEnum;
use App\Helpers\InstanceTrait;
use App\Models\Athlete;
use App\Models\EventCompetition;
use App\Models\Forecast;
use App\Models\ForecastSubmittedData;
use App\Models\User;

class ForecastDainisServiceHelper extends ForecastAbstractionHelper
{
    use InstanceTrait;

    protected EventCompetition $competition;
    protected bool $isTeamDiscipline = false;
    protected PointCalculatorServiceHelper $pointCalculator;

    protected float $mainPoints = 0;
    protected float $bonusPoints = 0;

    protected array $resultAthleteIds = [];
    protected array $userGivenForecastAthleteIds = [];

    public function calculateUserPoints(Forecast $forecast, User $user): self
    {
        $this->mainPoints = 0;
        $this->bonusPoints = 0;

        $this->competition = $forecast->competition;

        $this->isTeamDiscipline = DisciplineEnum::tryFrom($this->competition->discipline_remote_id)->isTeamDiscipline();

        $this->resultAthleteIds = collect($forecast->final_data->results)->pluck('tempId')->toArray();

        $this->userGivenForecastAthleteIds = collect($forecast->final_data->getUserByUserModel($user)->athletes)->pluck('tempId')->toArray();

        $this->registerMainPoints();
        $this->registerBonusPoints();

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

    protected function registerMainPoints(): self
    {
        $this->mainPoints = $this->getPrecisionPoints();
        return $this;
    }

    protected function registerBonusPoints(): self
    {
        $this->bonusPoints = 0;

        if($this->foundGoldPlace()){
            $this->bonusPoints += match ($this->isTeamDiscipline){
                false => 50,
                true => 100,
            };
        }

        if($this->foundSilverPlace()){
            $this->bonusPoints += match ($this->isTeamDiscipline){
                false => 35,
                true => 70,
            };
        }

        if($this->foundBronzePlace()){
            $this->bonusPoints += match ($this->isTeamDiscipline){
                false => 25,
                true => 50,
            };
        }

        return $this;
    }

    private function getPrecisionPoints(): int
    {
        $precisionPoints = 0;

        foreach ($this->userGivenForecastAthleteIds as $userSelectedPlace => $userGivenForecastAthleteId){

            $result = array_keys($this->resultAthleteIds, $userGivenForecastAthleteId);

            if(empty($result)){
                continue;
            }

            $athleteResultPlace = array_shift($result);

            $precisionPoints += $this->precisionDeltaPoints(
                $this->getPrecisionDelta(
                    userSelectedAthletePlace: $userSelectedPlace,
                    resultAthletePlace: $athleteResultPlace
                )
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
                0 => 50,
                1 => 35,
                2 => 25,
                3 => 15,
                4 => 10,
                5 => 8,
                default => 0
            };
        }

        return match ($precisionDelta){
            0 => 100,
            1 => 70,
            2 => 50,
            3 => 30,
            4 => 20,
            5 => 10,
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
}
