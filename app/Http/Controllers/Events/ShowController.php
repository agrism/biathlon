<?php

namespace App\Http\Controllers\Events;

use App\Helpers\Generic\GenericViewIndexHelper;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCompetition;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowController extends Controller
{
    public function __invoke(Request $request, string $id): View
    {
        $page = intval($request->query('page'));

        $page = max($page, 1);

        $event = Event::query()->where('event_remote_id', $id)->first();

        $this->registerBread('Event:'.$event->short_description);

        $data = EventCompetition::query()->where('event_id', $event->id)->paginate(perPage: 2000);

        return GenericViewIndexHelper::instance()
            ->setTitle('Event "'. $event->description.'" competitions')
            ->setData($data)
            ->setHeaders(['description', 'short','location', 'start', 'shootings','km'])
            ->setDataKeys([
                function (EventCompetition $competition): string {
                    return $this->getLink($competition, $competition->description);
                },
                function (EventCompetition $competition): string {
                    return $this->getLink($competition, $competition->short_description);
                },
                function (EventCompetition $competition): string {
                    return $this->getLink($competition, $competition->location);
                },
                function (EventCompetition $competition): string {
                    return $this->getLink($competition, $competition->start_time->format('d.m.Y H:i:s'));
                },
                function (EventCompetition $competition): string {
                    return $this->getLink($competition, $competition->nr_shootings);
                },
                function (EventCompetition $competition): string {
                    return $this->getLink($competition, $competition->km);
                },
            ])
            ->render();
    }

    protected function getLink(EventCompetition $competition, ?string $name): string
    {
        return '<a href="'.route('competitions.show', $competition->race_remote_id).'">'.$name.'</a>';
    }
}
