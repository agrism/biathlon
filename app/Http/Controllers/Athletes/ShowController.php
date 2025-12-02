<?php

namespace App\Http\Controllers\Athletes;

use App\Helpers\LinkHelper;
use App\Http\Controllers\Controller;
use App\Models\Athlete;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowController extends Controller
{
    public function __invoke(Request $request, string $id): View
    {
        $athlete = Athlete::query()->with([
            'results' => function (Relation $query) {
                return $query->orderByDesc('start_time')->with('competition');
            }
        ])->where('ibu_id', $id)->first();

        $this->registerBread('Athlete:'.$athlete->family_name);

        $linkHelper = app(LinkHelper::class);

        return view('athletes.show', compact('athlete', 'linkHelper'));
    }
}
