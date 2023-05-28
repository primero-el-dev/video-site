<?php

namespace App\Util;

class ArrayUtil
{
    public static function flatten(array $array): array
    {
        return array_reduce(
            $array, 
            fn($acc, $value) => array_merge($acc, is_array($value) ? self::flatten($value) : [$value]),
            []
        );
    }
}