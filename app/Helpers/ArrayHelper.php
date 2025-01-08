<?php

namespace App\Helpers;

class ArrayHelper
{
    use InstanceTrait;
    function toArray(mixed $data): array
    {
        if(is_array($data)){
            return $data;
        }

        return [$data];
    }
}
