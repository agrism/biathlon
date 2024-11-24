<?php

namespace App\Helpers;

use App\Enums\FavoriteTypeEnum;
use App\Models\Athlete;
use App\Models\Favorite;
use App\Models\User;

class RemoteImageRenderHelper
{
    use InstanceTrait;

    public function getImageTag(string $url, string $attributes = ""): string
    {
        if($this->checkImageExists($url)){
            return '<img src="'.$url.'" '.$attributes.' />';
        }

        return '<div>&#9737;&#9737;&#9737;&#9737;&#9737;</div>';
    }

    protected function checkImageExists(string $url): bool
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true); // Perform a HEAD request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2); // Timeout in seconds
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }
}
