<?php

namespace App\Http\Controllers\Competitions;

use App\Helpers\Generic\GenericViewIndexHelper;
use App\Helpers\LinkHelper;
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

    public function __invoke(Request $request, string $id, LinkHelper $linkHelper): View
    {
        $this->linkHelper = $linkHelper;

        $competition = EventCompetition::query()->where('race_remote_id', $id)->first();

        $this->registerBread('Competition:'.$competition->short_description);

        $data = EventCompetitionResult::query()->with('athlete')
            ->where('event_competition_id', $competition->id)
            ->paginate(perPage: 2000);

        return GenericViewIndexHelper::instance()
            ->setTitle('Race "'.$competition->description.'" results')
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
