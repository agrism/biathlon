<?php

namespace App\Http\Controllers\Forecasts;

use App\Enums\CompetitionCategoryEnum;
use App\Enums\DisciplineEnum;
use App\Enums\FavoriteIconEnum;
use App\Enums\FavoriteTypeEnum;
use App\Enums\GenderEnum;
use App\Enums\InputTypeEnum;
use App\Helpers\FavoriteHelper;
use App\Helpers\Generic\GenericViewIndexHelper;
use App\Helpers\LinkHelper;
use App\Helpers\NumberHelper;
use App\Http\Controllers\Controller;
use App\Models\Athlete;
use App\Models\Forecast;
use App\ValueObjects\Helpers\Generic\FilterValueObject;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class SelectAthleteController extends Controller
{
    const TODO = '<span style="font-size: 0.5rem;color: lightgray">Coming..<span>';

    const FILTER_COUNTRY = 'filter_country';
    const FILTER_NAME = 'filter_name';

    protected LinkHelper $linkHelper;

    public function __invoke(Request $request, string $id, string $place): View
    {
        GenericViewIndexHelper::instance()->saveFilterDataAll(request: $request, keys: [
            self::FILTER_COUNTRY,
            self::FILTER_NAME,
        ]);

        $this->linkHelper = LinkHelper::instance();

        if(!in_array($place, range(0,5))){
            throw new Exception('Place provided: '.$place);
        }

        /** @var Forecast $forecast */
        if(!$forecast = Forecast::query()->with('competition.event')->where('id', $id)->first()){
            throw new Exception('Forecast not found: '.$id);
        }

        $discipline = DisciplineEnum::tryFrom($forecast->competition->discipline_remote_id);

        $gender = CompetitionCategoryEnum::tryFrom($forecast->competition->cat_remote_id)->getGender();

        $athletes = Athlete::query();
        $athletes = $athletes->where('gender_id', $gender->value);
        $athletes = $athletes->where('is_team', $discipline->isTeamDiscipline() ? 1 : 0);
        if(!$discipline->isTeamDiscipline() ){
            $athletes = $athletes->where('functions', 'Athlete');
        }

        if($country = GenericViewIndexHelper::instance()->getFilterValue(self::FILTER_COUNTRY)){
            $athletes = $athletes->where('nat','LIKE', '%'.$country.'%');
        }

        if($name = GenericViewIndexHelper::instance()->getFilterValue(self::FILTER_NAME)){
            $athletes = $athletes->where(function($q) use($name){
                $q->where('given_name', 'LIKE', '%'.$name.'%')->orWhere('family_name', 'LIKE', '%'.$name.'%');
            });
        }

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
            ->setFilters([
                new FilterValueObject(
                    inputType: InputTypeEnum::TEXT,
                    key: self::FILTER_NAME,
                    title: 'Name',
                    value: GenericViewIndexHelper::instance()->getFilterValue(self::FILTER_NAME),
                    options: []
                ),
                new FilterValueObject(
                    inputType: InputTypeEnum::TEXT,
                    key: self::FILTER_COUNTRY,
                    title: 'Country (ISO2)',
                    value: GenericViewIndexHelper::instance()->getFilterValue(self::FILTER_COUNTRY),
                    options: []
                ),
//                new FilterValueObject(
//                    inputType: InputTypeEnum::SELECT,
//                    key: self::FILTER_COUNTRY,
//                    title: 'Country',
//                    value: GenericViewIndexHelper::instance()->getFilterValue(self::FILTER_COUNTRY),
//                    options: Athlete::query()->select('nat')->groupBy('nat')->orderBy('nat')->pluck('nat', 'nat')->all(),
//                )
            ])
            ->setFilterHtmxFormAttributes(attr: 'hx-get="'.route('forecasts.select-athlete', ['id' => $forecast->id, 'place' => $place]).'" hx-target="#selected-athletes"')
            ->render();
    }
}
