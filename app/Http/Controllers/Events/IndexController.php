<?php

namespace App\Http\Controllers\Events;

use App\Helpers\Generic\GenericViewIndexHelper;
use App\Helpers\LinkHelper;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndexController extends Controller
{

    protected LinkHelper $linkHelper;
    public function __invoke(Request $request, LinkHelper $linkHelper): View
    {
        $this->linkHelper = $linkHelper;

        $this->registerBread('Events');

        $data = Event::query()->with('season')->where('level', 1)->orderBy('start_date', 'desc')->paginate(perPage: 2000);

        return GenericViewIndexHelper::instance()
            ->setTitle('Events')
            ->setData($data)
            ->setHeaders([ 'status','season','description', 'Organizer','country','start', 'level'])
            ->setDataKeys([
                function (Event $event): string {
                    return $this->getLink(
                        $event,
                        $event->start_date->lt(now()) ? '<span style="color: darkgrey">completed</span>' : '<span style="color: darkgreen">upcoming</span>'
                    );
                },
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

                    $data = explode('/', $event->nat);

                    $return = [];

                    foreach ($data as $dataItem){
                        $return[] = '<img src="https://info.blob.core.windows.net/resources/bt/flags/'.mb_convert_case($dataItem, MB_CASE_LOWER).'.png" style="height:20px;display:block;" />';
                    }

                    return $this->getLink($event, implode('', $return));

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
        return $this->linkHelper->getLink(route: route('events.show', $event->event_remote_id),name:  $name);
    }
}
