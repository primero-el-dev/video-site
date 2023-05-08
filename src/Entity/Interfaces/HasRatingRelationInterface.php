<?php

namespace App\Entity\Interfaces;

use App\Entity\User;

interface HasRatingRelationInterface
{
    public function getLikesCount(): int;

    public function getDislikesCount(): int;

    public function getUserRating(User $user): ?int;
}