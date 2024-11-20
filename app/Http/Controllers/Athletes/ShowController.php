<?php

namespace App\Http\Controllers\Athletes;

use App\Helpers\Generic\GenericViewIndexHelper;
use App\Helpers\Generic\GenericViewShowHelper;
use App\Http\Controllers\Controller;
use App\Models\Athlete;
use App\Models\Event;
use App\Models\EventCompetition;
use App\Models\EventCompetitionResult;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowController extends Controller
{
    public function __invoke(Request $request, string $id): View
    {
        $page = intval($request->query('page'));

        $page = max($page, 1);

        $athlete = Athlete::query()->where('ibu_id', $id)->first();

        $this->registerBread('Athlete:'. $athlete->family_name);

        return view('athletes.show', compact('athlete'));

        return GenericViewShowHelper::instance()
            ->setTitle('Athlete: '. $athlete->family_name .' '. $athlete->given_name)
            ->setData($athlete)
            ->setHeaders(['','Family name:','Given name', 'Dob:','Age:','Gender:', 'Nationality:','Other name:' ,'Flag:'])
            ->setDataKeys([
                function() use($athlete){

                    try{
                        getimagesize($athlete->photo_uri);

                        return '<img src="'.$athlete->photo_uri.'" style="height: 200px" />';
                    } catch (\Exception){
                        return '';
                    }

                },
                'family_name',
                'given_name',
                function() use($athlete){
                    return $athlete->birth_date?->format('d.m.Y');
                },
                function() use($athlete){
                    return $athlete->birth_date?->age;
                },
                function() use($athlete){
                    return $athlete->gender_id;
                },
                function() use($athlete){
                    return $athlete->nat;
                },
                function() use($athlete){
                    return $athlete->other_family_names;
                },
                function() use($athlete){
                    return '<img src="'.$athlete->flag_uri.'" style="height: 15px" />';
                },

            ])
            ->render();
    }

    protected function getLink(EventCompetitionResult $result, string $name): string
    {
        return '<a href="'.route('competitions.show', $result->athlete_id).'">'.$name.'</a>';
    }
}
