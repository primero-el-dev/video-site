<?php

namespace App\Entity\UserSubjectAction;

use App\Entity\UserSubjectAction\UserSubjectAction;
use App\Entity\Interfaces\EntityInterface;
use App\Entity\User;
use App\Repository\UserSubjectAction\UserUserActionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Ignore;

#[UniqueEntity(['user', 'subject', 'action'])]
#[ORM\Entity(repositoryClass: UserUserActionRepository::class)]
class UserUserAction extends UserSubjectAction
{
    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'userActionsOnAnothers')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?User $user = null;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'otherUsersActions', targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    protected ?User $subject = null;

    public function getSubject(): ?User
    {
        return $this->subject;
    }

    public function setSubject(?User $subject): static
    {
        $this->subject = $subject;

        return $this;
    }
}