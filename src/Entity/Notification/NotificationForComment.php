<?php

namespace App\Entity\Notification;

use App\Entity\Comment;
use App\Entity\Notification\Notification;
use App\Repository\Notification\NotificationForCommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NotificationForCommentRepository::class)]
class NotificationForComment extends Notification
{
    public const SUBJECT_TYPE = 'comment';

    #[Groups(['notification'])]
    #[ORM\ManyToOne(targetEntity: Comment::class, inversedBy: 'notificationWhereSubject')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Comment $subject;

    public function getSubject(): ?Comment
    {
        return $this->subject;
    }

    public function setSubject(?Comment $subject): self
    {
        $this->subject = $subject;

        return $this;
    }
}