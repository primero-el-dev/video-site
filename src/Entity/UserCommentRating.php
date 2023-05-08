<?php

namespace App\Entity;

use App\Entity\Interfaces\HasRatingInterface;
use App\Entity\Interfaces\EntityInterface;
use App\Entity\Traits\RatingTrait;
use App\Repository\UserCommentRatingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[UniqueEntity(['user', 'comment'])]
#[ORM\Entity(repositoryClass: UserCommentRatingRepository::class)]
class UserCommentRating implements EntityInterface, HasRatingInterface
{
    use RatingTrait;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userCommentRatings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[Groups(['rating', 'user_comment_ratings'])]
    #[ORM\ManyToOne(inversedBy: 'userCommentRatings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Comment $comment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function setComment(?Comment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
