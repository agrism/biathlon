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

        $data = Event::query()
            ->with('season')
            ->where('level', 1)
            ->orderBy('start_date', 'desc')
            ->paginate(perPage: 20);

        return GenericViewIndexHelper::instance()
            ->setTitle('Events & World Cup Calendar')
            ->setData($data)
            ->setHeaders(['Status', 'Stage & Location', 'Season', 'Country', 'Start Date', 'Level'])
            ->setDataKeys([
                function (Event $event): string {
                    if ($event->start_date?->isToday()) {
                        $name = '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 text-xs font-bold border border-rose-200/60"><span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>Today</span>';
                    } elseif ($event->end_date && $event->end_date->isPast()) {
                        $name = '<span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">Completed</span>';
                    } elseif ($event->start_date && $event->start_date->isPast() && (!$event->end_date || $event->end_date->isFuture())) {
                        $name = '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-bold border border-amber-200/60"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>In Progress</span>';
                    } else {
                        $name = '<span class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200/60">Upcoming</span>';
                    }

                    return $this->getLink($event, $name);
                },
                function (Event $event): string {
                    return $this->getLink($event, e($event->description));
                },
                function (Event $event): string {
                    $name = $event->season?->name ?? '';
                    if (strlen($name) >= 4) {
                        $first = substr($name, 0, 2);
                        $second = substr($name, 2);
                        return $this->getLink($event, sprintf('%s/%s', $first, $second));
                    }
                    return $this->getLink($event, $name ?: '-');
                },
                function (Event $event): string {
                    if (!$event->nat) {
                        return e($event->nat_long ?: ($event->organizer ?: '-'));
                    }
                    $data = explode('/', $event->nat);
                    $return = [];
                    foreach ($data as $dataItem) {
                        $return[] = '<img src="https://info.blob.core.windows.net/resources/bt/flags/' . mb_convert_case($dataItem, MB_CASE_LOWER) . '.png" class="h-3.5 inline-block rounded-xs shadow-2xs mr-1" alt="' . e($dataItem) . '" />';
                    }
                    return $this->getLink($event, implode('', $return) . ' ' . e($event->nat_long ?: $event->organizer));
                },
                function (Event $event): string {
                    return $this->getLink($event, $event->start_date?->setTimeZone('Europe/Riga')->format('d M Y, H:i') ?? '-');
                },
                function (Event $event): string {
                    return $this->getLink($event, '<span class="px-2 py-0.5 rounded-md bg-sky-50 text-sky-700 font-bold text-xs">World Cup</span>');
                },
            ])
            ->render();
    }

    protected function getLink(Event $event, string $name): string
    {
        return $this->linkHelper->getLink(route: route('events.show', $event->event_remote_id), name: $name);
    }
}
