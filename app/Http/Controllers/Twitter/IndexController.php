<?php

namespace App\Http\Controllers\Twitter;

use App\Helpers\LinkHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndexController extends Controller
{
    protected LinkHelper $linkHelper;
    public function __invoke(Request $request): View
    {
        return view('twitter.index');
    }
}
