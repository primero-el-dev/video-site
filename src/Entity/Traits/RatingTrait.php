<?php

namespace App\Entity\Traits;

use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Interfaces\HasRatingInterface;

trait RatingTrait
{
    #[Groups(['rating'])]
    #[Assert\NotNull]
    #[Assert\Choice([HasRatingInterface::LIKE, HasRatingInterface::DISLIKE])]
    #[ORM\Column]
    protected ?int $rating = null;

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;

        return $this;
    }
}