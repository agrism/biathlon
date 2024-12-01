<?php

namespace Tests\Unit\Helpers\Forecasts;

use App\Helpers\Forecasts\PointCalculatorServiceHelper;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class PointCalculatorServiceHelperTest extends TestCase
{

    #[DataProvider('dataProvider')]
    public function test_calculator_methods(
        array $resultIds,
        array $userGivenIds,
        array $trueAsserts
    ): void {
        $calculator = PointCalculatorServiceHelper::instance()->calculate(
            resultIds: $resultIds,
            userGivenIds: $userGivenIds,
        );

        foreach ($trueAsserts as $calculatorMethod => $value) {
            $this->assertSame($value, $calculator->$calculatorMethod());
        }
    }

    public static function dataProvider(): array
    {
        return [
            [
                [1, 11, 12, 13, 14, 15],
                [1, 2, 3, 4, 5, 6],
                [
                    'foundGoldPlace' => true,
                    'foundSilverPlace' => false,
                    'foundBronzePlace' => false,
                    'found4Place' => false,
                    'found5Place' => false,
                    'found6Place' => false,
                    'foundBonus1Perfection' => false,
                    'foundBonus2None' => true,
                    'foundBonus2One' => false,
                    'foundBonus2Pair' => false,
                    'foundBonus2All' => false,
                    'foundBonus3None' => true,
                    'foundBonus3One' => false,
                    'foundBonus3Pair' => false,
                    'foundBonus3All' => false,
                ]
            ],
            [
                [1, 2, 12, 13, 14, 15],
                [1, 2, 3, 4, 5, 6],
                [
                    'foundGoldPlace' => true,
                    'foundSilverPlace' => true,
                    'foundBronzePlace' => false,
                    'found4Place' => false,
                    'found5Place' => false,
                    'found6Place' => false,
                    'foundBonus1Perfection' => false,
                    'foundBonus2None' => true,
                    'foundBonus2One' => false,
                    'foundBonus2Pair' => false,
                    'foundBonus2All' => false,
                    'foundBonus3None' => true,
                    'foundBonus3One' => false,
                    'foundBonus3Pair' => false,
                    'foundBonus3All' => false,
                ]
            ],
            [
                [1, 2, 3, 13, 14, 15],
                [1, 2, 3, 4, 5, 6],
                [
                    'foundGoldPlace' => true,
                    'foundSilverPlace' => true,
                    'foundBronzePlace' => true,
                    'found4Place' => false,
                    'found5Place' => false,
                    'found6Place' => false,
                    'foundBonus1Perfection' => true,
                    'foundBonus2None' => true,
                    'foundBonus2One' => false,
                    'foundBonus2Pair' => false,
                    'foundBonus2All' => false,
                    'foundBonus3None' => true,
                    'foundBonus3One' => false,
                    'foundBonus3Pair' => false,
                    'foundBonus3All' => false,
                ]
            ],
            [
                [1, 2, 3, 4, 14, 15],
                [1, 2, 3, 4, 5, 6],
                [
                    'foundGoldPlace' => true,
                    'foundSilverPlace' => true,
                    'foundBronzePlace' => true,
                    'found4Place' => true,
                    'found5Place' => false,
                    'found6Place' => false,
                    'foundBonus1Perfection' => true,
                    'foundBonus2None' => true,
                    'foundBonus2One' => false,
                    'foundBonus2Pair' => false,
                    'foundBonus2All' => false,
                    'foundBonus3None' => true,
                    'foundBonus3One' => false,
                    'foundBonus3Pair' => false,
                    'foundBonus3All' => false,
                ]
            ],
            [
                [1, 2, 3, 4, 5, 15],
                [1, 2, 3, 4, 5, 6],
                [
                    'foundGoldPlace' => true,
                    'foundSilverPlace' => true,
                    'foundBronzePlace' => true,
                    'found4Place' => true,
                    'found5Place' => true,
                    'found6Place' => false,
                    'foundBonus1Perfection' => true,
                    'foundBonus2None' => true,
                    'foundBonus2One' => false,
                    'foundBonus2Pair' => false,
                    'foundBonus2All' => false,
                    'foundBonus3None' => true,
                    'foundBonus3One' => false,
                    'foundBonus3Pair' => false,
                    'foundBonus3All' => false,
                ]
            ],
            [
                [1, 2, 3, 4, 5, 6],
                [1, 2, 3, 4, 5, 6],
                [
                    'foundGoldPlace' => true,
                    'foundSilverPlace' => true,
                    'foundBronzePlace' => true,
                    'found4Place' => true,
                    'found5Place' => true,
                    'found6Place' => true,
                    'foundBonus1Perfection' => true,
                    'foundBonus2None' => true,
                    'foundBonus2One' => false,
                    'foundBonus2Pair' => false,
                    'foundBonus2All' => false,
                    'foundBonus3None' => true,
                    'foundBonus3One' => false,
                    'foundBonus3Pair' => false,
                    'foundBonus3All' => false,
                ]
            ],
            [
                [1, 2, 3, 4, 5, 6],
                [2, 1, 30, 40, 50, 60],
                [
                    'foundGoldPlace' => false,
                    'foundSilverPlace' => false,
                    'foundBronzePlace' => false,
                    'found4Place' => false,
                    'found5Place' => false,
                    'found6Place' => false,
                    'foundBonus1Perfection' => false,
                    'foundBonus2None' => false,
                    'foundBonus2One' => false,
                    'foundBonus2Pair' => true,
                    'foundBonus2All' => false,
                    'foundBonus3None' => true,
                    'foundBonus3One' => false,
                    'foundBonus3Pair' => false,
                    'foundBonus3All' => false,
                ]
            ],
            [
                [1, 2, 3, 4, 5, 6],
                [2, 1, 3, 40, 50, 60],
                [
                    'foundGoldPlace' => false,
                    'foundSilverPlace' => false,
                    'foundBronzePlace' => true,
                    'found4Place' => false,
                    'found5Place' => false,
                    'found6Place' => false,
                    'foundBonus1Perfection' => false,
                    'foundBonus2None' => false,
                    'foundBonus2One' => false,
                    'foundBonus2Pair' => true,
                    'foundBonus2All' => false,
                    'foundBonus3None' => true,
                    'foundBonus3One' => false,
                    'foundBonus3Pair' => false,
                    'foundBonus3All' => false,
                ]
            ],
            [
                [3, 2, 1, 4, 5, 6],
                [2, 1, 3, 40, 50, 60],
                [
                    'foundGoldPlace' => false,
                    'foundSilverPlace' => false,
                    'foundBronzePlace' => false,
                    'found4Place' => false,
                    'found5Place' => false,
                    'found6Place' => false,
                    'foundBonus1Perfection' => false,
                    'foundBonus2None' => false,
                    'foundBonus2One' => false,
                    'foundBonus2Pair' => false,
                    'foundBonus2All' => true,
                    'foundBonus3None' => true,
                    'foundBonus3One' => false,
                    'foundBonus3Pair' => false,
                    'foundBonus3All' => false,
                ]
            ],
            [
                [3, 2, 1, 4, 5, 6],
                [2, 1, 3, 40, 5, 60],
                [
                    'foundGoldPlace' => false,
                    'foundSilverPlace' => false,
                    'foundBronzePlace' => false,
                    'found4Place' => false,
                    'found5Place' => true,
                    'found6Place' => false,
                    'foundBonus1Perfection' => false,
                    'foundBonus2None' => false,
                    'foundBonus2One' => false,
                    'foundBonus2Pair' => false,
                    'foundBonus2All' => true,
                    'foundBonus3None' => true,
                    'foundBonus3One' => false,
                    'foundBonus3Pair' => false,
                    'foundBonus3All' => false,
                ]
            ],
            [
                [3, 2, 1, 4, 5, 6],
                [2, 1, 3, 6, 5, 60],
                [
                    'foundGoldPlace' => false,
                    'foundSilverPlace' => false,
                    'foundBronzePlace' => false,
                    'found4Place' => false,
                    'found5Place' => true,
                    'found6Place' => false,
                    'foundBonus1Perfection' => false,
                    'foundBonus2None' => false,
                    'foundBonus2One' => false,
                    'foundBonus2Pair' => false,
                    'foundBonus2All' => true,
                    'foundBonus3None' => false,
                    'foundBonus3One' => true,
                    'foundBonus3Pair' => false,
                    'foundBonus3All' => false,
                ]
            ],
            [
                [3, 2, 1, 4, 5, 6],
                [2, 1, 3, 6, 5, 4],
                [
                    'foundGoldPlace' => false,
                    'foundSilverPlace' => false,
                    'foundBronzePlace' => false,
                    'found4Place' => false,
                    'found5Place' => true,
                    'found6Place' => false,
                    'foundBonus1Perfection' => false,
                    'foundBonus2None' => false,
                    'foundBonus2One' => false,
                    'foundBonus2Pair' => false,
                    'foundBonus2All' => true,
                    'foundBonus3None' => false,
                    'foundBonus3One' => false,
                    'foundBonus3Pair' => true,
                    'foundBonus3All' => false,
                ]
            ],
            [
                [3, 2, 1, 4, 5, 6],
                [2, 1, 3, 4, 5, 6],
                [
                    'foundGoldPlace' => false,
                    'foundSilverPlace' => false,
                    'foundBronzePlace' => false,
                    'found4Place' => true,
                    'found5Place' => true,
                    'found6Place' => true,
                    'foundBonus1Perfection' => false,
                    'foundBonus2None' => false,
                    'foundBonus2One' => false,
                    'foundBonus2Pair' => false,
                    'foundBonus2All' => true,
                    'foundBonus3None' => true,
                    'foundBonus3One' => false,
                    'foundBonus3Pair' => false,
                    'foundBonus3All' => false,
                ]
            ],
            [
                [1, 2, 3, 4, 5, 6],
                [6, 5, 4, 3, 2, 1],
                [
                    'foundGoldPlace' => false,
                    'foundSilverPlace' => false,
                    'foundBronzePlace' => false,
                    'found4Place' => false,
                    'found5Place' => false,
                    'found6Place' => false,
                    'foundBonus1Perfection' => false,
                    'foundBonus2None' => false,
                    'foundBonus2One' => false,
                    'foundBonus2Pair' => false,
                    'foundBonus2All' => true,
                    'foundBonus3None' => false,
                    'foundBonus3One' => false,
                    'foundBonus3Pair' => false,
                    'foundBonus3All' => true,
                ]
            ],
            [
                [1, 2, 3, 4, 5, 6],
                [4, 5, 6, 1, 2, 3],
                [
                    'foundGoldPlace' => false,
                    'foundSilverPlace' => false,
                    'foundBronzePlace' => false,
                    'found4Place' => false,
                    'found5Place' => false,
                    'found6Place' => false,
                    'foundBonus1Perfection' => false,
                    'foundBonus2None' => false,
                    'foundBonus2One' => false,
                    'foundBonus2Pair' => false,
                    'foundBonus2All' => true,
                    'foundBonus3None' => false,
                    'foundBonus3One' => false,
                    'foundBonus3Pair' => false,
                    'foundBonus3All' => true,
                ]
            ],
            [
                [1, 2, 3, 4, 5, 6],
                [4, 5, 6, 1, 2, 7],
                [
                    'foundGoldPlace' => false,
                    'foundSilverPlace' => false,
                    'foundBronzePlace' => false,
                    'found4Place' => false,
                    'found5Place' => false,
                    'found6Place' => false,
                    'foundBonus1Perfection' => false,
                    'foundBonus2None' => false,
                    'foundBonus2One' => false,
                    'foundBonus2Pair' => false,
                    'foundBonus2All' => true,
                    'foundBonus3None' => false,
                    'foundBonus3One' => false,
                    'foundBonus3Pair' => true,
                    'foundBonus3All' => false,
                ]
            ],
            [
                [1, 2, 3, 4, 5, 6],
                [8, 5, 6, 1, 9, 7],
                [
                    'foundGoldPlace' => false,
                    'foundSilverPlace' => false,
                    'foundBronzePlace' => false,
                    'found4Place' => false,
                    'found5Place' => false,
                    'found6Place' => false,
                    'foundBonus1Perfection' => false,
                    'foundBonus2None' => false,
                    'foundBonus2One' => false,
                    'foundBonus2Pair' => true,
                    'foundBonus2All' => false,
                    'foundBonus3None' => false,
                    'foundBonus3One' => true,
                    'foundBonus3Pair' => false,
                    'foundBonus3All' => false,
                ]
            ],
        ];
    }
}
