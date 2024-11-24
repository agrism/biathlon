<?php

namespace App\Http\Controllers\Favorites;

use App\Enums\FavoriteIconEnum;
use App\Enums\FavoriteTypeEnum;
use App\Helpers\FavoriteHelper;
use App\Http\Controllers\Controller;
use App\Models\Athlete;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;

class AthleteToggleController extends Controller
{
    public function __invoke(Request $request, string $id): View|Response
    {
        if(!$athlete = Athlete::query()->where('id', $id)->first()){
            return view('response-statuses.error', [
                'message' => 'Athlete not found!'
            ]);
        }

        $iFavorite = FavoriteHelper::instance()->toogle(
            user: auth()->user(),
            type: FavoriteTypeEnum::ATHLETE,
            typeId: $athlete->id,
        )->isFavoriteAfterAction();

        return view('response-statuses.favorites',[
            'favoriteIcon' => $iFavorite ? FavoriteIconEnum::ENABLED : FavoriteIconEnum::DISABLED,
        ]);

        return view('response-statuses.success');
    }
}
