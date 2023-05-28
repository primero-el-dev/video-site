<?php

namespace App\Entity\UserView;

use App\Entity\User;
use App\Entity\Video;
use App\Repository\UserView\UserVideoViewRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity(fields: ['subject', 'user'])]
#[UniqueEntity(fields: ['subject', 'ip'])]
#[ORM\Entity(repositoryClass: UserVideoViewRepository::class)]
class UserVideoView extends UserView
{
    #[ORM\ManyToOne(inversedBy: 'userVideoViews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Video $subject = null;

    #[ORM\ManyToOne(inversedBy: 'userVideoViews')]
    private ?User $user = null;

    public function getSubject(): ?Video
    {
        return $this->subject;
    }

    public function setSubject(?Video $subject): self
    {
        $this->subject = $subject;

        return $this;
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
}
