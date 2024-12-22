<?php

namespace App\Casts;

use App\Models\Athlete;
use App\ValueObjects\Athletes\AthleteDetailsValueObject;
use App\ValueObjects\Athletes\AthleteStatsDetailValueObject;
use App\ValueObjects\Athletes\Details\BadgeItemValueObject;
use App\ValueObjects\Athletes\Details\BibItemValueObject;
use App\ValueObjects\Athletes\Details\ItemValueObject;
use App\ValueObjects\Athletes\Details\OwgItemValueObject;
use App\ValueObjects\Athletes\Details\RaceItemValueObject;
use App\ValueObjects\Athletes\Details\RNKItemValueObject;
use App\ValueObjects\Helpers\Forecasts\ForecastFirstSixPlacesDataValueObject;
use Exception;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class AthleteStatsDetailsCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param array<string, mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $details = json_decode(data_get($attributes, 'stat_details'), true);

        return  static::createDetails($details);

    }

    /**
     * Prepare the given value for storage.
     *
     * @param array<string, mixed> $attributes
     * @throws Exception
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof AthleteStatsDetailValueObject) {
            return json_encode($value->export());
        }

        throw new Exception('incorrect instance passed 678');
    }

    public static function createDetails(array $details): AthleteStatsDetailValueObject
    {
        return New AthleteStatsDetailValueObject(
            statSeason: data_get($details, 'statSeason'),
            statsSeasonPointTotal: data_get($details, 'statsSeasonPointTotal'),
            statsSeasonPointsSprint: data_get($details, 'statsSeasonPointsSprint'),
            statsSeasonPointsIndividual: data_get($details, 'statsSeasonPointsIndividual'),
            statsSeasonPointsPursuit: data_get($details, 'statsSeasonPointsPursuit'),
            statsSeasonPointsMass: data_get($details, 'statsSeasonPointsMass'),
            statsSkiKmb: data_get($details, 'statsSkiKmb'),
            statSkiing: data_get($details, 'statSkiing'),
            statShooting: data_get($details, 'statShooting'),
            statShootingProne: data_get($details, 'statShootingProne'),
            statShootingStanding: data_get($details, 'statShootingStanding'),
        );
    }
}
