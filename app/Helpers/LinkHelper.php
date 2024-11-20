<?php

namespace App\Helpers;

class LinkHelper
{
    use InstanceTrait;

    public function getLink(string $route, ?string $name = null): string
    {
        return sprintf('<a href="%s">%s</a>', $route, $name);
    }
}
