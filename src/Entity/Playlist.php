<?php

namespace App\Entity;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\HasOwnerInterface;
use App\Entity\Notification\NotificationForPlaylist;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\SoftDeleteTrait;
use App\Repository\PlaylistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Uid\UuidV6;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: PlaylistRepository::class)]
class Playlist implements EntityInterface, HasOwnerInterface
{
    use CreatedAtTrait;
    use SoftDeleteTrait;

    #[Groups(['playlist'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?UuidV6 $id = null;

    #[Groups(['playlist'])]
    #[Assert\NotBlank(message: 'entity.playlist.name.notBlank')]
    #[Assert\Length(max: 255, maxMessage: 'entity.playlist.name.length.max')]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Groups(['playlist'])]
    #[Assert\Type(type: 'string', message: 'entity.playlist.description.type')]
    #[Assert\Length(max: 4095, maxMessage: 'entity.playlist.description.length.max')]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Groups(['playlist'])]
    #[ORM\ManyToOne(inversedBy: 'playlists')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[Groups(['playlist_items'])]
    #[ORM\OneToMany(mappedBy: 'playlist', targetEntity: PlaylistItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $playlistItems;

    #[ORM\OneToMany(mappedBy: 'subject', targetEntity: NotificationForPlaylist::class, fetch: 'EXTRA_LAZY')]
    private Collection $notificationsWhereSubject;

    public function __construct()
    {
        $this->playlistItems = new ArrayCollection();
    }

    public function getId(): ?UuidV6
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @param Collection<int, PlaylistItem>|PlaylistItem[] $playlistItems
     */
    public function setPlaylistItems(Collection|array $playlistItems): static
    {
        if (is_array($playlistItems)) {
            $playlistItems = new ArrayCollection($playlistItems);
        }

        $this->playlistItems = $playlistItems;

        return $this;
    }

    /**
     * @return Collection<int, PlaylistItem>
     */
    public function getPlaylistItems(): Collection
    {
        return $this->playlistItems;
    }

    public function addPlaylistItem(PlaylistItem $playlistItem): self
    {
        if (!$this->playlistItems->contains($playlistItem)) {
            $this->playlistItems->add($playlistItem);
            $playlistItem->setPlaylist($this);
        }

        return $this;
    }

    public function removePlaylistItem(PlaylistItem $playlistItem): self
    {
        if ($this->playlistItems->removeElement($playlistItem)) {
            if ($playlistItem->getPlaylist() === $this) {
                $playlistItem->setPlaylist(null);
            }
        }

        return $this;
    }

    public function removeAllPlaylistItems(): self
    {
        foreach ($this->playlistItems as $pi) {
            $this->removePlaylistItem($pi);
        }

        return $this;
    }

    /**
     * @return Collection<int, Playlist>
     */
    public function getNotificationsWhereSubject(): Collection
    {
        return $this->notificationsWhereSubject;
    }

    public function addNotificationsWhereSubject(NotificationForPlaylist $notificationsWhereSubject): self
    {
        if (!$this->notificationsWhereSubject->contains($notificationsWhereSubject)) {
            $this->notificationsWhereSubject->add($notificationsWhereSubject);
            $notificationsWhereSubject->setSubject($this);
        }

        return $this;
    }

    public function removeNotificationsWhereSubject(NotificationForPlaylist $notificationsWhereSubject): self
    {
        if ($this->notificationsWhereSubject->removeElement($notificationsWhereSubject)) {
            $notificationsWhereSubject->setSubject(null);
        }

        return $this;
    }
}
