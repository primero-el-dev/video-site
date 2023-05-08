<?php

namespace App\Entity;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\HasRatingInterface;
use App\Entity\Traits\RatingTrait;
use App\Repository\UserVideoRatingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[UniqueEntity(['user', 'video'])]
#[ORM\Entity(repositoryClass: UserVideoRatingRepository::class)]
class UserVideoRating implements EntityInterface, HasRatingInterface
{
    use RatingTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userVideoRatings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[Groups(['user_video_ratings'])]
    #[ORM\ManyToOne(inversedBy: 'userVideoRatings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Video $video = null;

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

    public function getVideo(): ?Video
    {
        return $this->video;
    }

    public function setVideo(?Video $video): self
    {
        $this->video = $video;

        return $this;
    }
}
