<?php

namespace App\Http\Controllers\Forecasts;

use App\Enums\FavoriteIconEnum;
use App\Enums\FavoriteTypeEnum;
use App\Helpers\FavoriteHelper;
use App\Helpers\Generic\GenericViewIndexHelper;
use App\Helpers\LinkHelper;
use App\Helpers\NumberHelper;
use App\Http\Controllers\Controller;
use App\Models\Athlete;
use App\Models\Forecast;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class SelectAthleteController extends Controller
{
    const TODO = '<span style="font-size: 0.5rem;color: lightgray">Coming..<span>';
    protected LinkHelper $linkHelper;

    public function __invoke(Request $request, string $id, string $place): View
    {
        $this->linkHelper = LinkHelper::instance();

        if(!in_array($place, range(0,5))){
            throw new \Exception('Place provided: '.$place);
        }

        if(!$forecast = Forecast::query()->where('id', $id)->first()){
            throw new \Exception('Forecast not found: '.$id);
        }

        $athletes = Athlete::query()
            ->where('gender_id', 'W')
            ->where('functions', 'Athlete');

        if(auth()->check()){
            $favoriteIds = FavoriteHelper::instance()
                ->getUserFavoriteIds(user: auth()->user(), type: FavoriteTypeEnum::ATHLETE);

            $athletes = $athletes->get()->map(function(Athlete $athlete) use($favoriteIds){
                $athlete->is_favorit = in_array($athlete->id, $favoriteIds);
                return $athlete;
            })
                ->sortByDesc(function(Athlete $athlete){
                    return $athlete->is_favorit;
                })
            ;

            $currentPage = request()->input('page', 1);

            $athletes = new LengthAwarePaginator(
                $athletes->forPage($currentPage, 30), // Slice the collection
                $athletes->count(), // Total items
                30, // Items per page
                $currentPage, // Current page
                ['path' => request()->url()] // Preserve the base URL
            );
        } else {
            $athletes = $athletes->paginate(30);
        }

        return GenericViewIndexHelper::instance()
            ->setTitle('Select Athlete for '.NumberHelper::instance()->ordinal($place+1) .' place!')
            ->setData($athletes)
            ->setHeaders(['Favourite','Add','WC points','Discipline points',	'Name',	'Country',	'Speed, %',	'Standing, %',	'Prone, %',	'Gold medals',	'Silver medals',	'Bronze medals',	'Top 10'])
            ->setDataKeys([
                function(Athlete $athlete): string{
                    return $this->linkHelper->getLink(
                        route: route('favorites.toggle', $athlete->id),
                        name: $athlete?->is_favorit ? FavoriteIconEnum::ENABLED->value : FavoriteIconEnum::DISABLED->value,
                        hrefProp: 'hx-get',
                        attributes: 'class="cursor-pointer"'
                    );
                },
                function(Athlete $athlete) use($place, $forecast): string{
//                    return $this->linkHelper->getLink(
//                        route: route('forecasts.select-athlete.submit', ['id' => $forecast->id, 'place' => $place, 'athlete' => $athlete->id]),
//                        name: '<span class="cursor-pointer">Add</span>',
//                        hrefProp: 'hx-get',
//                        attributes: 'hx-target=".cont" hx-indicator="#status"',
//                    );

                    $route = route('forecasts.select-athlete.submit', ['id' => $forecast->id, 'place' => $place, 'athlete' => $athlete->id]);
                    return <<<HTML
                            <button
                                class="bg-transparent hover:bg-blue-400 text-blue-500 hover:text-white py-0 px-2 border border-blue-500 hover:border-transparent rounded"
                                hx-get="$route"
                                hx-target="#selected-athletes"
                                hx-target=".cont"
                                hx-indicator="#status"
                            >
                                +
                            </button>
HTML;
                },
                function(Athlete $athlete): string{
                    return self::TODO;
                },
                function(Athlete $athlete): string{
                    return self::TODO;
                },
                function(Athlete $athlete): string{
                    return $athlete->given_name .' '. $athlete->family_name;
                },
                function(Athlete $athlete): string{
                    return $athlete->nat;
                },
                function(Athlete $athlete): string{
                    return self::TODO;
                },
                function(Athlete $athlete): string{
                    return self::TODO;
                },
                function(Athlete $athlete): string{
                    return self::TODO;
                },
                function(Athlete $athlete): string{
                    return self::TODO;
                },
                function(Athlete $athlete): string{
                    return self::TODO;
                },
                function(Athlete $athlete): string{
                    return self::TODO;
                },
                function(Athlete $athlete): string{
                    return self::TODO;
                },
            ])
            ->useHtmx()
            ->doNotUseLayout()
            ->htmxTargetElement('#selected-athletes')
            ->render();
    }
}
