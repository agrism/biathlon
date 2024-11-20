<?php

namespace App\Http\Controllers\Competitions;

use App\Helpers\Generic\GenericViewIndexHelper;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndexController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->registerBread('Competitions');

        $page = intval($request->query('page'));

        $page = max($page, 1);

        $data = Event::query()->with('season')->where('level', 1)->orderBy('start_date', 'desc')->paginate(perPage: 2000);

        return GenericViewIndexHelper::instance()
            ->setTitle('Events')
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
        return '<a href="'.route('events.show', $event->event_remote_id).'">'.$name.'</a>';
    }
}
