<?php

namespace App\Helpers;

class LinkHelper
{
    use InstanceTrait;

    public function getLink(string $route, ?string $name = null, ?string $style = null, string $hrefProp = 'href', ?string $attributes = null): string
    {
        return sprintf('<a  '.($hrefProp ?: 'href').'="%s" '.$attributes.' style="'.$style.'">%s</a>', $route, $name);
    }
}
