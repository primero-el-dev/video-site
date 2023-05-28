<?php

namespace App\Entity\Interfaces;

use App\Entity\Enum\UserActionEnum;
use App\Entity\User;

interface HasRatingInterface
{
    public function getLikesCount(): int;

    public function getDislikesCount(): int;

    public function getUserRating(User $user): ?UserActionEnum;
}