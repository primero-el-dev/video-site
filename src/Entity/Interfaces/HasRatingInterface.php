<?php

namespace App\Entity\Interfaces;

interface HasRatingInterface
{
    public const LIKE = 1;
    public const DISLIKE = -1;

    public function getRating(): ?int;

    public function setRating(int $rating): static;
}