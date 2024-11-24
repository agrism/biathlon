<?php

namespace App\Http\Controllers\Forecasts;

use App\Helpers\Generic\GenericViewIndexHelper;
use App\Helpers\LinkHelper;
use App\Http\Controllers\Controller;
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
                $s->where('level', 4);
            });

        if($authUserId = auth()->id()){
            $data = $data->with('submittedData');
        }

        $data = $data->orderBy('submit_deadline_at', 'desc')->paginate(perPage: 100);

        return GenericViewIndexHelper::instance()
            ->setTitle('Forecasts')
            ->setData($data)
            ->setHeaders(['competition','race start','submit deadline','status', 'my forecast'])
            ->setDataKeys([
                function (Forecast $forecast): string {

                    $name = [];
                    $name[] = $forecast->competition->event->organizer;
                    $name[] = $forecast->competition->start_time?->format('H:i d.m.Y');
                    $name[] = $forecast->competition->description;
                    $name = array_filter($name);
                    $name = array_map(function($s){
                        return str_replace(' ', '&ensp;', $s);
                    }, $name);

                    $name = implode(', ', $name);
                    return $this->getLink($forecast, $name);
                },
                function (Forecast $forecast): string {
                    return $this->getLink($forecast, $forecast->competition?->start_time->format('H:i d.m.Y'));
                },
                function (Forecast $forecast): string {
                    return $this->getLink($forecast, $forecast->submit_deadline_at->format('H:i d.m.Y'));
                },

                function (Forecast $forecast): string {
                    if($forecast->submit_deadline_at->gt(now())){
                        return $this->getLink($forecast, '<span style="color: green">upcoming</span>');
                    }
                    return $this->getLink($forecast, '<span style="color: grey;">closed</span>');
                },
                function(Forecast $forecast) use($authUserId){
                    if(!$authUserId){
                        return $this->linkHelper->getLink(route('login'), '<span style="color: grey;">Login</span>');
                    }

                    if($forecast->submittedData->count()){
                        return $this->getLink($forecast, '<span style="color: green;">Done</span>');
                    }
                    return $this->getLink($forecast, '<span style="color: red;">todo</span>');
                }

            ])
            ->render();
    }

    protected function getLink(Forecast $forecast, string $name, ?string $style = null): string
    {
        return $this->linkHelper->getLink(route: route('forecasts.show', $forecast->id),name:  $name, style: $style);
    }
}
