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
        ])->where(is_numeric($id) ? 'id' : 'ibu_id', $id)->first();

        if (!$athlete) {
            $athlete = Athlete::query()->with([
                'results' => function (Relation $query) {
                    return $query->orderByDesc('start_time')->with('competition');
                }
            ])->where('ibu_id', $id)->orWhere('id', $id)->firstOrFail();
        }

        $this->registerBread('Athlete: ' . $athlete->getFullName());

        $linkHelper = app(LinkHelper::class);

        if ($request->header('HX-Request') && !$request->header('HX-Boosted') && !$request->has('full_page')) {
            return view('athletes.partials.show-modal', compact('athlete', 'linkHelper'));
        }

        return view('athletes.show', compact('athlete', 'linkHelper'));
    }
}
