<?php

namespace App\Helpers;

class SeasonHelper
{
    use InstanceTrait;

    public function season(string $season): string
    {
        $first = substr($season, 0, 2);
        $second = substr($season, 2);
        return sprintf('%s/%s', $first, $second);
    }
}
