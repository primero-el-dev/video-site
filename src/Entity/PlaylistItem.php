<?php

namespace App\Entity;

use App\Entity\Interfaces\EntityInterface;
use App\Repository\PlaylistItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[UniqueEntity(['playlist', '`order`'])]
#[ORM\Entity(repositoryClass: PlaylistItemRepository::class)]
class PlaylistItem implements EntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'playlistItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Playlist $playlist = null;

    #[Groups(['playlist_item'])]
    #[ORM\Column(name: '`order`')]
    private ?int $order = null;

    #[Groups(['playlist_item'])]
    #[ORM\ManyToOne(inversedBy: 'playlistItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Video $video = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlaylist(): ?Playlist
    {
        return $this->playlist;
    }

    public function setPlaylist(?Playlist $playlist): self
    {
        $this->playlist = $playlist;

        return $this;
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function setOrder(int $order): self
    {
        $this->order = $order;

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
