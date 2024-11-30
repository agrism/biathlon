<?php

namespace App\Helpers\Forecasts;

use App\Enums\DisciplineEnum;
use App\Helpers\InstanceTrait;
use App\Models\Athlete;
use App\Models\EventCompetition;
use App\Models\Forecast;
use App\Models\ForecastSubmittedData;
use App\Models\User;

class ForecastFirstSixPlacesServiceHelper extends ForecastAbstractionHelper
{
    use InstanceTrait;

    protected EventCompetition $competition;
    protected bool $isTeamDiscipline = false;
    protected PointCalculatorServiceHelper $pointCalculator;

    protected float $mainPoints = 0;
    protected float $bonusPoints = 0;

    public function calculateUserPoints(Forecast $forecast, User $user): self
    {
        /** @var ForecastSubmittedData $submittedData */
        if (!$submittedData = ForecastSubmittedData::query()
            ->with('forecast.competition.results.athlete')
            ->where('forecast_id', $forecast->id)
            ->where('user_id', $user->id)
            ->first()) {
            return $this;
        }

        $this->competition = $submittedData->forecast->competition;

        $this->isTeamDiscipline = DisciplineEnum::tryFrom($this->competition->discipline_remote_id)->isTeamDiscipline();

        $raceResultFirstSixPlaces = $this->competition->results->sortBy('rank')->take(6)
            ->pluck('athlete')
            ->map(function (Athlete $athlete): Athlete {
                $athlete->temp_id = $athlete->is_team ? $athlete->nat : $athlete->id;
                return $athlete;
            })->pluck('temp_id')->all();

        $userGivenFirstSixPlaces = collect($submittedData->submitted_data->athletes)->map(function (Athlete $athlete) {
            $athlete->temp_id = $athlete->is_team ? $athlete->nat : $athlete->id;
            return $athlete;
        })->pluck('temp_id')->all();


        $this->pointCalculator = PointCalculatorServiceHelper::instance()->calculate(
            resultIds: $raceResultFirstSixPlaces,
            userGivenIds: $userGivenFirstSixPlaces
        );

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
        $this->mainPoints += $this->pointCalculator->foundGoldPlace() ? $this->getPoint(individualPoints: 25, teamPoints: 15) : 0;
        $this->mainPoints += $this->pointCalculator->foundSilverPlace() ? $this->getPoint(individualPoints: 20, teamPoints: 12) : 0;
        $this->mainPoints += $this->pointCalculator->foundBronzePlace() ? $this->getPoint(individualPoints: 15, teamPoints: 8) : 0;
        $this->mainPoints += $this->pointCalculator->found4Place() ? $this->getPoint(individualPoints: 5, teamPoints: 4) : 0;
        $this->mainPoints += $this->pointCalculator->found5Place() ? $this->getPoint(individualPoints: 5, teamPoints: 4) : 0;
        $this->mainPoints += $this->pointCalculator->found6Place() ? $this->getPoint(individualPoints: 5, teamPoints: 4) : 0;
        return $this;
    }

    protected function registerBonusPoints(): self
    {
        $this->bonusPoints += $this->pointCalculator->foundBonus1Perfection() ? $this->getPoint(individualPoints: 25, teamPoints: 10) : 0;
        $this->bonusPoints += $this->pointCalculator->foundBonus2None() ? $this->getPoint(individualPoints: 0, teamPoints: 0) : 0;
        $this->bonusPoints += $this->pointCalculator->foundBonus2One() ? $this->getPoint(individualPoints: 5, teamPoints: 2) : 0;
        $this->bonusPoints += $this->pointCalculator->foundBonus2Pair() ? $this->getPoint(individualPoints: 12, teamPoints: 5) : 0;
        $this->bonusPoints += $this->pointCalculator->foundBonus2All() ? $this->getPoint(individualPoints: 20, teamPoints: 10) : 0;

        $this->bonusPoints += $this->pointCalculator->foundBonus3None() ? $this->getPoint(individualPoints: 0, teamPoints: 0) : 0;
        $this->bonusPoints += $this->pointCalculator->foundBonus3One() ? $this->getPoint(individualPoints: 2, teamPoints: 1) : 0;
        $this->bonusPoints += $this->pointCalculator->foundBonus3Pair() ? $this->getPoint(individualPoints: 5, teamPoints: 2) : 0;
        $this->bonusPoints += $this->pointCalculator->foundBonus3All() ? $this->getPoint(individualPoints: 10, teamPoints: 4) : 0;
        return $this;
    }

    protected function getPoint(float $individualPoints, float $teamPoints): float
    {
        return $this->isTeamDiscipline ? $teamPoints : $individualPoints;
    }
}
