<?php

namespace App\Entity\UserSubjectAction;

use App\Entity\Comment;
use App\Entity\UserSubjectAction\UserSubjectAction;
use App\Entity\User;
use App\Repository\UserSubjectAction\UserCommentActionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Ignore;

#[UniqueEntity(['user', 'subject', 'action'])]
#[ORM\Entity(repositoryClass: UserCommentActionRepository::class)]
class UserCommentAction extends UserSubjectAction
{
    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'userCommentActions')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?User $user = null;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'userCommentActions', targetEntity: Comment::class)]
    #[ORM\JoinColumn(nullable: false)]
    protected ?Comment $subject = null;

    public function getSubject(): ?Comment
    {
        return $this->subject;
    }

    public function setSubject(?Comment $subject): static
    {
        $this->subject = $subject;

        return $this;
    }
}