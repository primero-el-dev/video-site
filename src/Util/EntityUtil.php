<?php

namespace App\Util;

use App\Entity\Interfaces\EntityInterface;

class EntityUtil
{
    public static function areSame(?EntityInterface $first, ?EntityInterface $second): bool
    {
        return (!$first && !$second) || ($first && $second && $first->getId() === $second->getId());
    }
}