<?php

namespace App\Http\Controllers\Events;

use App\Helpers\Generic\GenericViewIndexHelper;
use App\Helpers\LinkHelper;
use App\Helpers\SeasonHelper;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCompetition;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowController extends Controller
{
    protected LinkHelper $linkHelper;

    public function __invoke(Request $request, string $id, LinkHelper $linkHelper, SeasonHelper $seasonHelper): View
    {
        $this->linkHelper = $linkHelper;

        $event = Event::query()->with('season')->where('event_remote_id', $id)->first();

        $this->registerBread('Event:' . $event->short_description);

        $data = EventCompetition::query()
            ->where('event_id', $event->id)
            ->paginate(perPage: 2000);


        $title = [];
        $title[] = $event->description;
        if($event->event_series_no){
            $title[] = 'stage '.trim($event->event_series_no);
        }
        $title[] = $event->organizer;
        $title[] = $event->nat_long;
        $title[] = $this->getDates($event);
        $title = implode(', ', $title);

        return GenericViewIndexHelper::instance()
            ->setTitle($title)
            ->setData($data)
            ->setHeaders(['description', 'short', 'location', 'start', 'shootings', 'km'])
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

    protected function getLink(EventCompetition $competition, ?string $name = null): string
    {
        return $this->linkHelper->getLink(
            route: route('competitions.show', $competition->race_remote_id),
            name: $name,
        );
    }

    protected function getDates(Event $event): ?string
    {
        $start = $event->start_date;
        $end = $event->end_date;

        if($start->format('m') == $end->format('m')){
            return sprintf('%s-%s', $start->format('d'), $end->format('d.m.Y'));
        }

        if($start->format('Y') == $end->format('Y')){
            return sprintf('%s-%s', $start->format('d.m'), $end->format('d.m.Y'));
        }

        return sprintf('%s-%s', $start->format('d.m.y'), $end->format('d.m.Y'));
    }
}
