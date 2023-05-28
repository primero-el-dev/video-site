<?php

namespace App\Entity\Notification;

use App\Entity\Video;
use App\Entity\Notification\Notification;
use App\Repository\Notification\NotificationForVideoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NotificationForVideoRepository::class)]
class NotificationForVideo extends Notification
{
    public const SUBJECT_TYPE = 'video';

    #[Groups(['notification'])]
    #[ORM\ManyToOne(targetEntity: Video::class, inversedBy: 'notificationWhereSubject')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Video $subject;

    public function getSubject(): ?Video
    {
        return $this->subject;
    }

    public function setSubject(?Video $subject): self
    {
        $this->subject = $subject;

        return $this;
    }
}