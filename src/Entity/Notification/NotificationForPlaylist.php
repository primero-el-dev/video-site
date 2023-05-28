<?php

namespace App\Entity\Notification;

use App\Entity\Notification\Notification;
use App\Entity\Playlist;
use App\Repository\Notification\NotificationForPlaylistRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NotificationForPlaylistRepository::class)]
class NotificationForPlaylist extends Notification
{
    public const SUBJECT_TYPE = 'playlist';

    #[Groups(['notification'])]
    #[ORM\ManyToOne(targetEntity: Playlist::class, inversedBy: 'notificationWhereSubject')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Playlist $subject;

    public function getSubject(): ?Playlist
    {
        return $this->subject;
    }

    public function setSubject(?Playlist $subject): self
    {
        $this->subject = $subject;

        return $this;
    }
}