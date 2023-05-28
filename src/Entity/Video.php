<?php

namespace App\Entity;

use App\Entity\UserView\UserVideoView;
use App\Entity\Enum\UserActionEnum;
use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\HasOwnerInterface;
use App\Entity\Interfaces\HasRatingInterface;
use App\Entity\Interfaces\HasUserActionRelationInterface;
use App\Entity\Notification\NotificationForVideo;
use App\Entity\Traits\CreatedAtTrait;
use App\Repository\VideoRepository;
use App\Entity\Traits\SoftDeleteTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Uid\UuidV6;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\UserSubjectAction\UserVideoAction;

#[Gedmo\SoftDeleteable]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: VideoRepository::class)]
class Video implements EntityInterface, HasOwnerInterface, HasRatingInterface, HasUserActionRelationInterface
{
    use CreatedAtTrait;
    use SoftDeleteTrait;
    
    public const WAITING_TO_ACCEPT_BY_USER_STATUS = 'waiting_to_accept_by_user';
    public const WAITING_TO_ACCEPT_BY_ADMIN_STATUS = 'waiting_to_accept_by_admin';
    public const PUBLISHED_STATUS = 'published';

    #[Groups(['video', 'video_extended'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?UuidV6 $id = null;

    #[Groups(['video', 'video_extended'])]
    #[Assert\NotBlank(message: 'entity.video.name.notBlank')]
    #[Assert\Length(max: 255, maxMessage: 'entity.video.name.length.max')]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Groups(['video', 'video_extended'])]
    #[Assert\Length(max: 255, maxMessage: 'entity.video.description.length.max')]
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[Groups(['video', 'video_extended'])]
    #[ORM\Column(length: 255)]
    private ?string $snapshotPath = null;

    #[Groups(['video', 'video_extended'])]
    #[ORM\Column(length: 255)]
    private ?string $videoPath = null;

    #[Groups(['video_user'])]
    #[ORM\ManyToOne(inversedBy: 'videos', fetch: 'EAGER')]
    private ?User $owner = null;

    #[Groups(['video', 'video_extended'])]
    #[ORM\Column(length: 255)]
    private string $status = self::WAITING_TO_ACCEPT_BY_ADMIN_STATUS;

    #[Groups(['video', 'video_extended'])]
    #[ORM\Column]
    private ?float $snapshotTimestamp = null;

    #[Groups(['video', 'video_extended'])]
    #[ORM\Column]
    private ?float $sampleStartTimestamp = null;

    #[Groups(['video', 'video_extended'])]
    #[ORM\ManyToMany(mappedBy: 'videos', targetEntity: Tag::class, cascade: ['persist'])]
    private Collection $tags;

    #[Groups(['video_comments'])]
    #[ORM\OneToMany(mappedBy: 'video', targetEntity: Comment::class, fetch: 'EXTRA_LAZY')]
    private Collection $comments;

    #[Groups(['video_user_actions'])]
    #[ORM\OneToMany(mappedBy: 'subject', targetEntity: UserVideoAction::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private Collection $userVideoActions;

    #[ORM\OneToMany(mappedBy: 'subject', targetEntity: UserVideoView::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private Collection $userVideoViews;

    #[ORM\OneToMany(mappedBy: 'subject', targetEntity: NotificationForVideo::class, fetch: 'EXTRA_LAZY')]
    private Collection $notificationsWhereSubject;

    #[ORM\OneToMany(mappedBy: 'video', targetEntity: PlaylistItem::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private Collection $playlistItems;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->userVideoActions = new ArrayCollection();
        $this->userVideoViews = new ArrayCollection();
        $this->notificationsWhereSubject = new ArrayCollection();
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

    public function getSnapshotPath(): ?string
    {
        return $this->snapshotPath;
    }

    public function setSnapshotPath(string $snapshotPath): self
    {
        $this->snapshotPath = $snapshotPath;

        return $this;
    }

    public function getVideoPath(): ?string
    {
        return $this->videoPath;
    }

    public function setVideoPath(string $videoPath): self
    {
        $this->videoPath = $videoPath;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSnapshotTimestamp(): ?float
    {
        return $this->snapshotTimestamp;
    }

    public function setSnapshotTimestamp(float $snapshotTimestamp): self
    {
        $this->snapshotTimestamp = $snapshotTimestamp;

        return $this;
    }

    public function getSampleStartTimestamp(): ?float
    {
        return $this->sampleStartTimestamp;
    }

    public function setSampleStartTimestamp(float $sampleStartTimestamp): self
    {
        $this->sampleStartTimestamp = $sampleStartTimestamp;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addVideo($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeVideo($this);
        }

        return $this;
    }

    #[Groups(['video_extended'])]
    public function isDeleted(): bool
    {
        return (bool) $this->deletedAt;
    }

    #[Groups(['rating'])]
    public function getLikesCount(): int
    {
        return $this->getActionsCount(UserActionEnum::LIKE);
    }

    #[Groups(['rating'])]
    public function getDislikesCount(): int
    {
        return $this->getActionsCount(UserActionEnum::DISLIKE);
    }

    public function getActionsCount(UserActionEnum $action): int
    {
        return $this->userVideoActions->filter(fn($uvr) => $uvr->getAction() === $action)->count();
    }

    #[Groups(['rating'])]
    public function getUserRating(User $user): ?UserActionEnum
    {
        $result = $this->userVideoActions
            ->filter(fn($uvr) => $uvr->getUser()->getId() === $user->getId() 
                && in_array($uvr->getAction(), [UserActionEnum::LIKE, UserActionEnum::DISLIKE]))
            ->first();
        
        return $result ? $result->getAction() : null;
    }

    #[Groups(['video'])]
    public function getCountRootComments(): int
    {
        return $this->comments->filter(fn($c) => $c->getParent() === null)->count();
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setVideo($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getVideo() === $this) {
                $comment->setVideo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserVideoAction>
     */
    public function getUserSubjectActions(): Collection
    {
        return $this->userVideoActions;
    }

    /**
     * @return Collection<int, UserVideoAction>
     */
    public function getUserVideoActions(): Collection
    {
        return $this->userVideoActions;
    }

    public function addUserVideoAction(UserVideoAction $userVideoAction): self
    {
        if (!$this->userVideoActions->contains($userVideoAction)) {
            $this->userVideoActions->add($userVideoAction);
            $userVideoAction->setSubject($this);
        }

        return $this;
    }

    public function removeUserVideoAction(UserVideoAction $userVideoAction): self
    {
        if ($this->userVideoActions->removeElement($userVideoAction)) {
            if ($userVideoAction->getSubject() === $this) {
                $userVideoAction->setSubject(null);
            }
        }

        return $this;
    }

    public function getUserActions(User $user): Collection|array
    {
        $data = $this->userVideoActions
            ->filter(fn($uva) => $uva->getUser()->getId() === $user->getId())
            ->toArray();

        return array_values($data);
        
    }

    /**
     * @return Collection<int, UserVideoView>
     */
    public function getUserVideoViews(): Collection
    {
        return $this->userVideoViews;
    }

    public function addUserVideoView(UserVideoView $userVideoView): self
    {
        if (!$this->userVideoViews->contains($userVideoView)) {
            $this->userVideoViews->add($userVideoView);
            $userVideoView->setSubject($this);
        }

        return $this;
    }

    public function removeUserVideoView(UserVideoView $userVideoView): self
    {
        if ($this->userVideoViews->removeElement($userVideoView)) {
            if ($userVideoView->getSubject() === $this) {
                $userVideoView->setSubject(null);
            }
        }

        return $this;
    }

    #[Groups(['views'])]
    public function getViewsCount(): int
    {
        return $this->userVideoViews->count();
    }

    /**
     * @return Collection<int, NotificationForVideo>
     */
    public function getNotificationsWhereSubject(): Collection
    {
        return $this->notificationsWhereSubject;
    }

    public function addNotificationsWhereSubject(NotificationForVideo $notificationsWhereSubject): self
    {
        if (!$this->notificationsWhereSubject->contains($notificationsWhereSubject)) {
            $this->notificationsWhereSubject->add($notificationsWhereSubject);
            $notificationsWhereSubject->setSubject($this);
        }

        return $this;
    }

    public function removeNotificationsWhereSubject(NotificationForVideo $notificationsWhereSubject): self
    {
        if ($this->notificationsWhereSubject->removeElement($notificationsWhereSubject)) {
            $notificationsWhereSubject->setSubject(null);
        }

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
            $playlistItem->setVideo($this);
        }

        return $this;
    }

    public function removePlaylistItem(PlaylistItem $playlistItem): self
    {
        if ($this->playlistItems->removeElement($playlistItem)) {
            if ($playlistItem->getVideo() === $this) {
                $playlistItem->setVideo(null);
            }
        }

        return $this;
    }
}
