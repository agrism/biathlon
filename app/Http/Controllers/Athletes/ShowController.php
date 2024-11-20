<?php

namespace App\Http\Controllers\Athletes;

use App\Http\Controllers\Controller;
use App\Models\Athlete;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowController extends Controller
{
    public function __invoke(Request $request, string $id): View
    {
        $athlete = Athlete::query()->where('ibu_id', $id)->first();

        $this->registerBread('Athlete:'. $athlete->family_name);

        return view('athletes.show', compact('athlete'));
    }
}
