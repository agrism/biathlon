<?php

namespace App\Http\Controllers\Athletes;

use App\Helpers\Generic\GenericViewIndexHelper;
use App\Helpers\LinkHelper;
use App\Http\Controllers\Controller;
use App\Models\Athlete;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndexController extends Controller
{
    protected LinkHelper $linkHelper;
    public function __invoke(Request $request, LinkHelper $linkHelper): View
    {
        $this->linkHelper = $linkHelper;

        $this->registerBread('Season 24/25Athletes');


        dd(Athlete::query()
            ->with('results.competition')
            ->limit(1)
            ->get());

        dd(
            Event::query()
                ->with(['season'=> function($q){
                    $q->where('name', 2425);
                }])
                ->where('level', 1)
                ->with('competitions.results')
                ->get()
        );

        $data = Event::query()
            ->with('season')
            ->where('level', 1)
            ->with('competitions.results')
            ->orderBy('start_date', 'desc')
            ->paginate(perPage: 2000);

        return GenericViewIndexHelper::instance()
            ->setTitle('Athletes')
            ->setData($data)
            ->setHeaders(['season','description', 'Organizer','start', 'level'])
            ->setDataKeys([
                function (Event $event): string {
                    $name = $event->season->name;
                    $first = substr($name, 0, 2);
                    $second = substr($name, 2);

                    return $this->getLink($event, sprintf('%s/%s', $first, $second));
                },
                function (Event $event): string {
                    return $this->getLink($event, $event->description);
                },
                function (Event $event): string {
                    return $this->getLink($event, $event->nat_long);
                },
                function (Event $event): string {
                    return $this->getLink($event, $event->start_date->format('d.m.Y'));
                },
                function (Event $event): string {
                    return $this->getLink($event, $event->level);
                },
            ])
            ->render();
    }

    protected function getLink(Event $event, string $name): string
    {
        return $this->linkHelper->getLink(route: route('events.show', $event->event_remote_id), name:  $name);
    }
}
