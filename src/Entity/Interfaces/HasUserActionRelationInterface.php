<?php

namespace App\Entity\Interfaces;

use App\Entity\Interfaces\HasUserActionInterface;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;

interface HasUserActionRelationInterface
{
    /**
     * @return HasUserActionInterface[]
     */
    public function getUserActions(User $user): Collection|array;
}