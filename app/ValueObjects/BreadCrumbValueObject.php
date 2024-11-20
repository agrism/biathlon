<?php

namespace App\ValueObjects;

use Illuminate\Routing\Route;

class BreadCrumbValueObject
{
    public function __construct(
        public string $name,
        public string $route,
        public string $title
    )
    {

    }
}
