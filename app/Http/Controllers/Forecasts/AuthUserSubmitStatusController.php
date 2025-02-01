<?php

namespace App\Http\Controllers\Forecasts;

use App\Enums\MoveDirectionEnum;
use App\Helpers\LinkHelper;
use App\Helpers\SeasonHelper;
use App\Http\Controllers\Controller;
use App\Models\Forecast;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\AthleteValueObject;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\UserValueObject;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;

class AuthUserSubmitStatusController extends Controller
{
    public function __invoke(Request $request, string $id): View|Response
    {
        if(!$userId = auth()?->user()?->id){
            abort(401, 'Login first');
        }

        if(!$forecast = Forecast::query()->where('id', $id)->first()){
            abort(404);
        }

        /** @var UserValueObject $user */
        $user = collect($forecast->final_data->users)->where('id', $userId)->first();

        $isCompleted = collect($user?->athletes ?: [])->filter(function(AthleteValueObject $athlete): bool{
            return !empty($athlete->id);
        })->count() > 5;

        return view('components.status-is-customer-completed-forecast-data', compact('isCompleted'));
    }
}
