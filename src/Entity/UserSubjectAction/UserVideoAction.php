<?php

namespace App\Entity\UserSubjectAction;

use App\Entity\UserSubjectAction\UserSubjectAction;
use App\Entity\Interfaces\EntityInterface;
use App\Entity\User;
use App\Entity\Video;
use App\Repository\UserSubjectAction\UserVideoActionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Ignore;

#[UniqueEntity(['user', 'subject', 'action'])]
#[ORM\Entity(repositoryClass: UserVideoActionRepository::class)]
class UserVideoAction extends UserSubjectAction
{
    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'userVideoActions')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?User $user = null;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'userVideoActions', targetEntity: Video::class)]
    #[ORM\JoinColumn(nullable: false)]
    protected ?Video $subject = null;

    public function getSubject(): ?Video
    {
        return $this->subject;
    }

    public function setSubject(?Video $subject): static
    {
        $this->subject = $subject;

        return $this;
    }
}