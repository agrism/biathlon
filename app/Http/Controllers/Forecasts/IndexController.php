<?php

namespace App\Http\Controllers\Forecasts;

use App\Enums\Forecast\ForecastStatusEnum;
use App\Helpers\Generic\GenericViewIndexHelper;
use App\Helpers\LinkHelper;
use App\Http\Controllers\Controller;
use App\Models\Athlete;
use App\Models\EventCompetition;
use App\Models\Forecast;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\AthleteValueObject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndexController extends Controller
{

    protected LinkHelper $linkHelper;
    public function __invoke(Request $request, LinkHelper $linkHelper): View
    {
        $this->linkHelper = $linkHelper;

        $this->registerBread('Predictions');

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
        $counter = 1;

        return GenericViewIndexHelper::instance()
            ->setTitle('Predictions')
            ->setData($data)
            ->setHeaders(['no','competition','race start','status', 'my forecast'])
            ->setDataKeys([
                function (Forecast $forecast) use(&$counter): int {
                    return $counter++;
                },
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

                    if($forecast->submit_deadline_at->gt(now())){

                    $name = '<span style="color: green">Starts in '. str_replace(' from now','',$forecast->submit_deadline_at->diffForHumans()) .'</span>';

                        if($forecast->competition->results->count()){
                            return $this->linkHelper->getLink(route: route('competitions.show', $forecast->competition->race_remote_id), name: $name. ' (start list)');
                        }

                        return $this->getLink($forecast, $name);
                    }

                    if($forecast->status == ForecastStatusEnum::COMPLETED){
                        return $this->getLink($forecast, '<span style="color: grey;">closed</span>');
                    }

                    return $this->getLink($forecast, '<span style="color: grey;">Waiting results</span>');
                },
                function(Forecast $forecast) use($authUserId){
                    if(!$authUserId){
                        return $this->linkHelper->getLink(route('login'), '<span style="color: grey;">Login</span>');
                    }

                    if($forecast->status == ForecastStatusEnum::COMPLETED){
                        return $this->getLink($forecast, '<span style="color: grey;">Finished</span>');
                    }

                    $user = $forecast->final_data->getUserByUserModel(auth()->user());

                    if($forecast->submit_deadline_at->gt(now())){

                        $submittedAthleteCount = collect($user->athletes)->filter(fn(AthleteValueObject $athlete) => !empty($athlete->id))->count();

                        if($submittedAthleteCount <6){
                            return $this->getLink($forecast, '<span style="color: red;">Todo, add '.(6-$submittedAthleteCount).' athlete(s) to the list</span>');
                        }
                      return $this->getLink($forecast, '<span style="color: green;">Ready for race</span>');
                    }

                    return $this->getLink($forecast, '<span style="color: grey;">Waiting results</span>');
                }
            ])
            ->render();
    }

    protected function getLink(Forecast $forecast, string $name, ?string $style = null): string
    {
        return $this->linkHelper->getLink(route: route('forecasts.show', $forecast->id),name:  $name, style: $style);
    }
}
