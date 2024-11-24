<?php

namespace App\ValueObjects\Helpers\Forecasts;

use App\Models\Athlete;

class ForecastFirstSixPlacesDataValueObject extends ForecastDataAbstractionValueObject
{
    /**
     * @param ?Athlete[] $athletes
     */
    public array $athletes = [];

    public function __construct(array $athletes = [])
    {
        $a = [];

        foreach (range(0,5) as $index){
            $a[$index] = data_get($athletes, $index, new Athlete);
        }

        $this->athletes = $a;
    }
}
