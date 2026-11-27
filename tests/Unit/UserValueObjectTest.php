<?php

namespace Tests\Unit;

use App\Enums\Forecast\AwardPointEnum;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\AthleteValueObject;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\PointValueObject;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\UserValueObject;
use PHPUnit\Framework\TestCase;

class UserValueObjectTest extends TestCase
{
    public function test_get_points_for_athlete_with_stored_point_details(): void
    {
        $pointDetails = [
            0 => [
                new PointValueObject(type: AwardPointEnum::REGULAR_POINT, value: 9),
                new PointValueObject(type: AwardPointEnum::BONUS_POINT, value: 0),
            ],
            1 => [
                new PointValueObject(type: AwardPointEnum::REGULAR_POINT, value: 7),
                new PointValueObject(type: AwardPointEnum::BONUS_POINT, value: 7),
            ],
            2 => [
                new PointValueObject(type: AwardPointEnum::REGULAR_POINT, value: 0),
                new PointValueObject(type: AwardPointEnum::BONUS_POINT, value: 0),
            ],
        ];

        $user = new UserValueObject(
            id: 1,
            name: 'Test User',
            pointDetails: $pointDetails,
        );

        $pts0 = $user->getPointsForAthlete(0);
        $this->assertEquals(9.0, $pts0['regular']);
        $this->assertEquals(0.0, $pts0['bonus']);
        $this->assertEquals(9.0, $pts0['total']);

        $pts1 = $user->getPointsForAthlete(1);
        $this->assertEquals(7.0, $pts1['regular']);
        $this->assertEquals(7.0, $pts1['bonus']);
        $this->assertEquals(14.0, $pts1['total']);

        $pts2 = $user->getPointsForAthlete(2);
        $this->assertEquals(0.0, $pts2['regular']);
        $this->assertEquals(0.0, $pts2['bonus']);
        $this->assertEquals(0.0, $pts2['total']);

        // Out of bounds index
        $pts99 = $user->getPointsForAthlete(99);
        $this->assertEquals(0.0, $pts99['regular']);
        $this->assertEquals(0.0, $pts99['bonus']);
        $this->assertEquals(0.0, $pts99['total']);
    }

    public function test_get_points_for_athlete_fallback_calculation(): void
    {
        $athletes = [
            0 => new AthleteValueObject(id: 101, tempId: 'ATH_1', name: 'Athlete 1', flagUrl: null, stats: null),
            1 => new AthleteValueObject(id: 102, tempId: 'ATH_2', name: 'Athlete 2', flagUrl: null, stats: null),
            2 => new AthleteValueObject(id: 103, tempId: 'ATH_3', name: 'Athlete 3', flagUrl: null, stats: null),
        ];

        $results = [
            new AthleteValueObject(id: 101, tempId: 'ATH_1', name: 'Athlete 1', flagUrl: null, stats: null), // Finished 1st (index 0) => predicted 1st => delta 0 => 21 reg + 21 gold bonus = 42
            new AthleteValueObject(id: 999, tempId: 'ATH_X', name: 'Athlete X', flagUrl: null, stats: null), // Finished 2nd (index 1)
            new AthleteValueObject(id: 102, tempId: 'ATH_2', name: 'Athlete 2', flagUrl: null, stats: null), // Finished 3rd (index 2) => predicted 2nd (index 1) => delta 1 => 15 reg + 0 bonus = 15
        ];

        $user = new UserValueObject(
            id: 2,
            name: 'Fallback User',
            athletes: $athletes,
            pointDetails: [], // empty point details triggers fallback
        );

        $pts0 = $user->getPointsForAthlete(0, $results, false);
        $this->assertEquals(21.0, $pts0['regular']);
        $this->assertEquals(21.0, $pts0['bonus']);
        $this->assertEquals(42.0, $pts0['total']);

        $pts1 = $user->getPointsForAthlete(1, $results, false);
        $this->assertEquals(15.0, $pts1['regular']);
        $this->assertEquals(0.0, $pts1['bonus']);
        $this->assertEquals(15.0, $pts1['total']);

        // Athlete 3 did not finish in results
        $pts2 = $user->getPointsForAthlete(2, $results, false);
        $this->assertEquals(0.0, $pts2['regular']);
        $this->assertEquals(0.0, $pts2['bonus']);
        $this->assertEquals(0.0, $pts2['total']);
    }
}
