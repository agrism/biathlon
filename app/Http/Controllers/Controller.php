<?php

namespace App\Http\Controllers;

use App\Helpers\BreadCrumbHelper;
use App\ValueObjects\BreadCrumbValueObject;

abstract class Controller
{
//    public function __construct()
//    {
////        dump();
//        BreadCrumbHelper::instance()->register(
//            new BreadCrumbValueObject(
//                name: request()->route()->getName(),
//                route: request()->url(),
//                title: static::class
//            )
//        );
//    }

    protected function registerBread(string $title = 'x', ?string $name = null, ?string $route = null): self
    {
        BreadCrumbHelper::instance()->register(
            new BreadCrumbValueObject(
                name: $name ?? request()->route()?->getName() ?? '',
                route: $route ?? request()->url(),
                title: $title,
            )
        );
        return  $this;
    }
}
