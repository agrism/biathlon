<?php

namespace App\Casts;

use App\Enums\Forecast\AwardPointEnum;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\AthleteValueObject;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\FinalDataValueObject;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\PointValueObject;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\UserValueObject;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class ForecastFinalDataCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param array<string, mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $finalData = json_decode(data_get($attributes, 'final_data'), true);
        return new FinalDataValueObject(
            results: collect(data_get($finalData, 'results', []))->map(fn($athlete) => new AthleteValueObject(
                id: data_get($athlete, 'id'),
                tempId: data_get($athlete, 'tempId'),
                name: data_get($athlete, 'name'),
                flagUrl: data_get($athlete, 'flagUrl'),
            ))->toArray(),
            users: collect(data_get($finalData, 'users', []))->map(fn($user) => new UserValueObject(
                id: data_get($user, 'id'),
                name: data_get($user, 'name'),
                points: collect(data_get($user, 'points', []))->map(fn($point) => new PointValueObject(
                    type: AwardPointEnum::tryFrom(data_get($point, 'type')),
                    value: data_get($point, 'value'),
                ))->toArray(),
                athletes: collect(data_get($user, 'athletes', []))->map(fn($athlete) => new AthleteValueObject(
                    id: data_get($athlete, 'id'),
                    tempId: data_get($athlete, 'tempId'),
                    name: data_get($athlete, 'name'),
                    flagUrl: data_get($athlete, 'flagUrl'),
                ))->toArray(),
            ))->toArray()
        );
    }

    /**
     * Prepare the given value for storage.
     *
     * @param array<string, mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof FinalDataValueObject) {

            return json_encode($value->export());
        }

        throw new \Exception('incorrect instance passed 12399');
    }
}
