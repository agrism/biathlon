<?php

namespace App\Http\Controllers\Forecasts;

use App\Helpers\Generic\GenericViewIndexHelper;
use App\Helpers\LinkHelper;
use App\Helpers\NumberHelper;
use App\Http\Controllers\Controller;
use App\Models\Athlete;
use App\Models\Forecast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class SelectAthleteController extends Controller
{
    protected LinkHelper $linkHelper;

    public function __invoke(Request $request, string $id, string $place, LinkHelper $linkHelper): View
    {
        $this->linkHelper = $linkHelper;
        $authUserId = auth()->id();


        if(!in_array($place, range(0,5))){
            throw new \Exception('Place provided: '.$place);
        }

        if(!$forecast = Forecast::query()->where('id', $id)->first()){
            throw new \Exception('Forecast not found: '.$id);
        }

        $athletes = Athlete::query()
            ->where('gender_id', 'W')
            ->where('functions', 'Athlete');

        $data = $athletes->paginate(perPage: 30);

        return GenericViewIndexHelper::instance()
            ->setTitle('Select Athlete for '.NumberHelper::instance()->ordinal($place+1) .' place!')
            ->setData($data)
            ->setHeaders(['Favourite','WCh points','Discipline points',	'Name',	'Country',	'Speed, %',	'Standing, %',	'Prone, %',	'Gold medals',	'Silver medals',	'Bronze medals',	'Top 10'])
            ->setDataKeys([
                function(Athlete $athlete): string{
                    return '-';
                },
                function(Athlete $athlete): string{
                    return '-';
                },
                function(Athlete $athlete) use($place, $forecast): string{
                    return $this->linkHelper->getLink(
                        route: route('forecasts.select-athlete.submit', ['id' => $forecast->id, 'place' => $place, 'athlete' => $athlete->id]),
                        name: '<span class="cursor-pointer">submit</span>',
                        hrefProp: 'hx-get',
                        attributes: 'hx-target="body"',
                    );
                },
                function(Athlete $athlete): string{
                    return $athlete->given_name .' '. $athlete->family_name;
                },
                function(Athlete $athlete): string{
                    return $athlete->nat;
                },
                function(Athlete $athlete): string{
                    return '-';
                },
                function(Athlete $athlete): string{
                    return '-';
                },
                function(Athlete $athlete): string{
                    return '-';
                },
                function(Athlete $athlete): string{
                    return '-';
                },
                function(Athlete $athlete): string{
                    return '-';
                },
                function(Athlete $athlete): string{
                    return '-';
                },
                function(Athlete $athlete): string{
                    return '-';
                },
            ])
            ->useHtmx()
            ->doNotUseLayout()
            ->htmxTargetElement('#selected-athletes')
            ->render();
    }
}
