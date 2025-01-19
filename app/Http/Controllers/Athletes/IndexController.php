<?php

namespace App\Http\Controllers\Athletes;

use App\Enums\FavoriteIconEnum;
use App\Enums\FavoriteTypeEnum;
use App\Enums\InputTypeEnum;
use App\Helpers\FavoriteHelper;
use App\Helpers\Generic\GenericViewIndexHelper;
use App\Helpers\LinkHelper;
use App\Http\Controllers\Controller;
use App\Models\Athlete;
use App\ValueObjects\Helpers\Generic\FilterValueObject;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class IndexController extends Controller
{
    const TODO = '<span style="font-size: 0.5rem;color: lightgray">Coming..<span>';

    const FILTER_COUNTRY = 'filter_country';
    const FILTER_NAME = 'filter_name';

    protected LinkHelper $linkHelper;

    public function __invoke(Request $request): View
    {
        $this->registerBread('Athletes');

        GenericViewIndexHelper::instance()->saveFilterDataAll(request: $request, keys: [
            self::FILTER_COUNTRY,
            self::FILTER_NAME,
        ]);

        $this->linkHelper = LinkHelper::instance();

        $athletes = Athlete::query();

        if ($country = GenericViewIndexHelper::instance()->getFilterValue(self::FILTER_COUNTRY)) {
            $athletes = $athletes->where('nat', 'LIKE', '%' . $country . '%');
        }

        if ($name = GenericViewIndexHelper::instance()->getFilterValue(self::FILTER_NAME)) {
            $athletes = $athletes->where(function ($q) use ($name) {
                $q->where('given_name', 'LIKE', '%' . $name . '%')->orWhere('family_name', 'LIKE', '%' . $name . '%');
            });
        }

        if (auth()->check()) {
            $favoriteIds = FavoriteHelper::instance()
                ->getUserFavoriteIds(user: auth()->user(), type: FavoriteTypeEnum::ATHLETE);

            $athletes = $athletes->get()->map(function (Athlete $athlete) use ($favoriteIds) {
                $athlete->is_favorit = in_array($athlete->id, $favoriteIds);
                return $athlete;
            })
                ->sortByDesc(function (Athlete $athlete) {
                    return $athlete?->is_favorit;
                });


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
            ->setTitle('Athletes')
            ->setData($athletes)
            ->setHeaders([
                'Favourite <span style="color: lightgray">&uarr;</span><span style="color: black;">&darr;</span>',
                'Add',
                'WC points',
                'Discipline points',
                'Name ',
                'Country',
                'Speed, %',
                'Speed behind, k/h',
                'Standing, %;',
                'Prone, %',
                'Gold medals',
                'Silver medals',
                'Bronze medals',
                'Top 10'
            ])
            ->setDataKeys([
                function (Athlete $athlete): string {
                    return $this->linkHelper->getLink(
                        route: route('favorites.toggle', $athlete->id),
                        name: $athlete?->is_favorit ? FavoriteIconEnum::ENABLED->value : FavoriteIconEnum::DISABLED->value,
                        hrefProp: 'hx-get',
                        attributes: 'class="cursor-pointer"'
                    );
                },
                function (Athlete $athlete): string {
                    return 1;
                },
                function (Athlete $athlete): float {
                    return floatval($athlete->stat_p_total);
                },
                function (Athlete $athlete): float {
                    return 1;
                },
                function (Athlete $athlete): string {
                    return $athlete->given_name . ' ' . $athlete->family_name;
                },
                function (Athlete $athlete): string {
                    return $athlete->nat;
                },
                function (Athlete $athlete): string {
                    if ($athlete->stat_skiing === null) {
                        return '-';
                    }
                    return intval($athlete->stat_skiing);
                },
                function (Athlete $athlete): string {
                    if ($athlete->stat_ski_kmb === null) {
                        return '-';
                    }
                    return floatval($athlete->stat_ski_kmb) * -1;
                },
                function (Athlete $athlete): string {
                    return floatval($athlete->stat_shooting_standing);
                },
                function (Athlete $athlete): string {
                    return floatval($athlete->stat_shooting_prone);
                },
                function (Athlete $athlete): string {
                    return self::TODO;
                },
                function (Athlete $athlete): string {
                    return self::TODO;
                },
                function (Athlete $athlete): string {
                    return self::TODO;
                },
                function (Athlete $athlete): string {
                    return self::TODO;
                },
            ])
            ->useHtmx()
//            ->doNotUseLayout()
            ->htmxTargetElement('body')
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
                    title: 'Country (ISO3)',
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
            ->setFilterHtmxFormAttributes(attr: 'hx-get="' . route('athletes.index') . '" hx-target="body"')
            ->render();
    }
}
