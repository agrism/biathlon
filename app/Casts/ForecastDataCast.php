<?php

namespace App\Casts;

use App\Models\Athlete;
use App\ValueObjects\Helpers\Forecasts\ForecastFirstSixPlacesDataValueObject;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class ForecastDataCast implements CastsAttributes
{
    const PROP_ATHLETES = 'athletes';
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $submittedData = json_decode(data_get($attributes, 'submitted_data'), true);
        $submittedData = data_get($submittedData, self::PROP_ATHLETES);
        return new ForecastFirstSixPlacesDataValueObject(
            collect($submittedData)->map(function(?int $id): Athlete{
                if(!$id){
                    return new Athlete;
                }
                return Athlete::query()->where('id', $id)->first();
            })->all()
        );
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if($value instanceof ForecastFirstSixPlacesDataValueObject){
            return json_encode([
                self::PROP_ATHLETES => collect($value->athletes)->map(function(?Athlete $athlete): ?int{
                    return $athlete?->id;
                })->all()
            ]);
        }

        throw new \Exception('incorrect instance passed 123');
    }
}
