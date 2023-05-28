<?php

namespace App\Entity\Notification;

use App\Entity\User;
use App\Entity\Notification\Notification;
use App\Repository\Notification\NotificationForUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NotificationForUserRepository::class)]
class NotificationForUser extends Notification
{
    public const SUBJECT_TYPE = 'user';

    #[Groups(['notification'])]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'notificationWhereSubject')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $subject;

    public function getSubject(): ?User
    {
        return $this->subject;
    }

    public function setSubject(?User $subject): self
    {
        $this->subject = $subject;

        return $this;
    }
}