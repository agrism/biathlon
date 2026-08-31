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
                $athletes->forPage($currentPage, 30),
                $athletes->count(),
                30,
                $currentPage,
                ['path' => request()->url()]
            );
        } else {
            $athletes = $athletes->orderByDesc('stat_p_total')->paginate(30);
        }

        return GenericViewIndexHelper::instance()
            ->setTitle('Athletes')
            ->setData($athletes)
            ->setHeaders([
                'Fav',
                'Athlete',
                'Country',
                'WC points',
                'Ski Speed (s/km)',
                'Prone %',
                'Standing %',
            ])
            ->setDataKeys([
                function (Athlete $athlete): string {
                    return $this->linkHelper->getLink(
                        route: route('favorites.toggle', $athlete->id),
                        name: $athlete?->is_favorit ? '<span class="text-amber-400">★</span>' : '<span class="text-slate-300">☆</span>',
                        hrefProp: 'hx-get',
                        attributes: 'class="cursor-pointer text-base hover:scale-125 transition-transform inline-block"'
                    );
                },
                function (Athlete $athlete): string {
                    $route = route('athletes.show', $athlete->id);
                    return '<button type="button" hx-get="' . $route . '" hx-target="#show" hx-boost="false" class="font-bold text-slate-900 hover:text-sky-600 hover:underline cursor-pointer text-left inline-flex items-center gap-1.5">' . $athlete->given_name . ' ' . $athlete->family_name . '</button>';
                },
                function (Athlete $athlete): string {
                    $flag = $athlete->flag_uri ? '<img src="'.$athlete->flag_uri.'" class="h-3.5 rounded-2xs inline-block mr-1">' : '';
                    return '<span class="inline-flex items-center text-xs font-semibold uppercase text-slate-600">'.$flag.$athlete->nat.'</span>';
                },
                function (Athlete $athlete): string {
                    return $athlete->stat_p_total !== null ? '<span class="font-bold text-slate-800">'.floatval($athlete->stat_p_total).'</span>' : '<span class="text-slate-400">-</span>';
                },
                function (Athlete $athlete): string {
                    if ($athlete->stat_ski_kmb === null) {
                        return '<span class="text-slate-400">-</span>';
                    }
                    return '<span class="font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded text-xs">-'.floatval($athlete->stat_ski_kmb).'s/km</span>';
                },
                function (Athlete $athlete): string {
                    if ($athlete->stat_shooting_prone === null) {
                        return '<span class="text-slate-400">-</span>';
                    }
                    return '<span class="font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-xs">'.floatval($athlete->stat_shooting_prone).'%</span>';
                },
                function (Athlete $athlete): string {
                    if ($athlete->stat_shooting_standing === null) {
                        return '<span class="text-slate-400">-</span>';
                    }
                    return '<span class="font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-xs">'.floatval($athlete->stat_shooting_standing).'%</span>';
                },
            ])
            ->setShowRouteName('athletes.show')
            ->useHtmx()
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
            ])
            ->setFilterHtmxFormAttributes(attr: 'hx-get="' . route('athletes.index') . '" hx-target="body"')
            ->render();
    }
}
