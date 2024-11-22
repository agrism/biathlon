<?php

namespace App\Http\Controllers\Competitions;

use App\Helpers\Generic\GenericViewIndexHelper;
use App\Helpers\LinkHelper;
use App\Helpers\SeasonHelper;
use App\Http\Controllers\Controller;
use App\Models\Athlete;
use App\Models\Event;
use App\Models\EventCompetition;
use App\Models\EventCompetitionResult;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowController extends Controller
{
    protected LinkHelper $linkHelper;

    public function __invoke(Request $request, string $id, LinkHelper $linkHelper, SeasonHelper $seasonHelper): View
    {
        $this->linkHelper = $linkHelper;

        $competition = EventCompetition::query()
            ->with('event.season')
            ->where('race_remote_id', $id)->first();

        $this->registerBread('Competition:'.$competition->short_description);

        $data = EventCompetitionResult::query()->with('athlete')
            ->where('event_competition_id', $competition->id)
            ->paginate(perPage: 2000);

        $title = [];
        $title[] = $competition->description;
        $title[] = $competition->event->description;
        if($competition->event->event_series_no){
            $title[] = 'stage '.trim($competition->event->event_series_no);
        }
        $title[] = $competition->event->organizer;
        $title[] = $competition->event->nat_long;
        $title[] = $competition->start_time?->format('H:i d.m.Y');
        $title = array_filter($title);

        $title = array_map(function ($item){
            return str_replace(' ', '&nbsp;', $item);
        }, $title);

        $title = implode(', ', $title);

        return GenericViewIndexHelper::instance()
            ->setTitle('Results: ' .$title)
            ->setData($data)
            ->setHeaders(['rank','bib','Athlete','Nat','flag','shooting','behind', 'wc points'])
            ->setDataKeys([
                function (EventCompetitionResult $result): string {
                    return $this->getLink($result, $result->rank ?: '-');
                },
                function (EventCompetitionResult $result): string {
                    return $this->getLink($result, $result->bib ?: '-');
                },
                function (EventCompetitionResult $result): string {
                    return $this->getLink($result, $result->athlete?->family_name .' '.$result->athlete?->given_name);
                },
                function (EventCompetitionResult $result): string {
                    return $this->getLink($result, $result->athlete?->nat ?:'-');
                },
                function (EventCompetitionResult $result): string {
                    return $this->getLink($result, '<img src="'.$result->athlete->flag_uri.'" style="height:20px;" />');
                },
                function (EventCompetitionResult $result): string {
                    return $this->getLink($result, $result->shootings ?: '-');
                },
                function (EventCompetitionResult $result): string {
                    return $this->getLink($result, $result->behind ?: '-');
                },
                function (EventCompetitionResult $result): string {
                    return $this->getLink($result, $result->wc ?: '-');
                },
            ])
            ->render();
    }

    protected function getLink(EventCompetitionResult $result, string $name): string
    {
        return $this->linkHelper->getLink(route: route('athletes.show', $result->athlete->ibu_id), name: $name);
    }
}
