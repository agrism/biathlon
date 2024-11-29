<?php

namespace App\Http\Controllers\Forecasts;

use App\Helpers\Generic\GenericViewIndexHelper;
use App\Helpers\LinkHelper;
use App\Http\Controllers\Controller;
use App\Models\Athlete;
use App\Models\EventCompetition;
use App\Models\Forecast;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndexController extends Controller
{

    protected LinkHelper $linkHelper;
    public function __invoke(Request $request, LinkHelper $linkHelper): View
    {
        $this->linkHelper = $linkHelper;

        $this->registerBread('Forecasts');

//        $c = Forecast::query()->with('competition')->first();
//
//        dump($c);
//
//        dd($c->competition);

        $data = Forecast::query()
            ->with('competition.event')
            ->whereHas('competition.event', function ($s){
                $s->where('level', 1);
            });

        if($authUserId = auth()->id()){
            $data = $data->with('submittedData');
        }

        $data = $data->orderBy('submit_deadline_at', 'asc')->paginate(perPage: 100);

        return GenericViewIndexHelper::instance()
            ->setTitle('Forecasts')
            ->setData($data)
            ->setHeaders(['competition','race start','submit deadline','status', 'my forecast'])
            ->setDataKeys([
                function (Forecast $forecast): string {

                    $name = [];
                    $name[] = $forecast->competition->event->organizer;
                    $name[] = $forecast->competition->start_time?->setTimeZone('Europe/RIga')->format('d F Y, H:i');
                    $name[] = $forecast->competition->description;
                    $name = array_filter($name);
                    $name = array_map(function($s){
                        return str_replace(' ', '&ensp;', $s);
                    }, $name);

                    $name = implode(', ', $name);
                    return $this->getLink($forecast, $name);
                },
                function (Forecast $forecast): string {
                    return $this->getLink($forecast, $forecast->competition?->start_time->setTimeZone('Europe/RIga')->format('d F Y, H:i'));
                },
                function (Forecast $forecast): string {
                    return $this->getLink($forecast, $forecast->submit_deadline_at->setTimeZone('Europe/RIga')->format('d F Y, H:i'));
                },

                function (Forecast $forecast): string {
                    if($forecast->submit_deadline_at->gt(now())){
                        return $this->getLink($forecast, '<span style="color: green">Starts in '. str_replace(' from now','',$forecast->submit_deadline_at->diffForHumans()) .'</span>');
                    }
                    return $this->getLink($forecast, '<span style="color: grey;">closed</span>');
                },
                function(Forecast $forecast) use($authUserId){
                    if(!$authUserId){
                        return $this->linkHelper->getLink(route('login'), '<span style="color: grey;">Login</span>');
                    }

                    $authUserSubmittedAthletes = $forecast->submittedData->where('user_id', auth()->id())->first()?->submitted_data?->athletes ?? [];

                    $authUserSubmittedAthletes = collect($authUserSubmittedAthletes)->filter(function(Athlete $athlete){
                        return !empty($athlete->family_name);
                    })->count();

                    if($authUserSubmittedAthletes < 1){
                        return $this->getLink($forecast, '<span style="color: darkslategrey;">Waiting for your bids</span>');
                    }

                    if($authUserSubmittedAthletes < 6){
                        return $this->getLink($forecast, '<span style="color: darkgoldenrod;">Done partially</span>');
                    }

                    return $this->getLink($forecast, '<span style="color: green;">Done</span>');
                }
            ])
            ->render();
    }

    protected function getLink(Forecast $forecast, string $name, ?string $style = null): string
    {
        return $this->linkHelper->getLink(route: route('forecasts.show', $forecast->id),name:  $name, style: $style);
    }
}
