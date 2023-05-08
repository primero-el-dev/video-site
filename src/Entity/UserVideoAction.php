<?php

namespace App\Entity;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\HasUserActionInterface;
use App\Entity\Traits\UserActionTrait;
use App\Repository\UserVideoActionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Ignore;

#[UniqueEntity(['user', 'video', 'action'])]
#[ORM\Entity(repositoryClass: UserVideoActionRepository::class)]
class UserVideoAction implements EntityInterface, HasUserActionInterface
{
    use UserActionTrait;

    #[Ignore]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'userVideoActions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'userVideoActions')]
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
