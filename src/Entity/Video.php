<?php

namespace App\Entity;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\HasOwnerInterface;
use App\Entity\Interfaces\HasRatingInterface;
use App\Entity\Interfaces\HasRatingRelationInterface;
use App\Entity\Interfaces\HasUserActionRelationInterface;
use App\Entity\Traits\CreatedAtTrait;
use App\Repository\VideoRepository;
use App\Entity\Traits\SoftDeleteTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;
use Symfony\Component\Uid\UuidV7;
use Gedmo\Mapping\Annotation as Gedmo;

#[Gedmo\SoftDeleteable]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: VideoRepository::class)]
class Video implements EntityInterface, HasOwnerInterface, HasRatingRelationInterface, HasUserActionRelationInterface
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
    private ?UuidV7 $id = null;

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

    #[Groups(['video', 'video_extended'])]
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
    #[ORM\ManyToMany(targetEntity: Tag::class, mappedBy: 'videos', cascade: ['persist'])]
    private Collection $tags;

    #[Groups(['video_user_ratings'])]
    #[ORM\OneToMany(mappedBy: 'video', targetEntity: UserVideoRating::class)]
    private Collection $userVideoRatings;

    #[Groups(['video_comments'])]
    #[ORM\OneToMany(mappedBy: 'video', targetEntity: Comment::class)]
    private Collection $comments;

    #[Groups(['video_user_actions'])]
    #[ORM\OneToMany(mappedBy: 'video', targetEntity: UserVideoAction::class, orphanRemoval: true)]
    private Collection $userVideoActions;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->userVideoRatings = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->userVideoActions = new ArrayCollection();
    }

    public function getId(): ?UuidV7
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

    /**
     * @return Collection<int, UserVideoRating>
     */
    public function getUserVideoRatings(): Collection
    {
        return $this->userVideoRatings;
    }

    public function addUserVideoRating(UserVideoRating $userVideoRating): self
    {
        if (!$this->userVideoRatings->contains($userVideoRating)) {
            $this->userVideoRatings->add($userVideoRating);
            $userVideoRating->setVideo($this);
        }

        return $this;
    }

    public function removeUserVideoRating(UserVideoRating $userVideoRating): self
    {
        if ($this->userVideoRatings->removeElement($userVideoRating)) {
            if ($userVideoRating->getVideo() === $this) {
                $userVideoRating->setVideo(null);
            }
        }

        return $this;
    }

    #[Groups(['rating'])]
    public function getLikesCount(): int
    {
        return $this->getRatingsCount(HasRatingInterface::LIKE);
    }

    #[Groups(['rating'])]
    public function getDislikesCount(): int
    {
        return $this->getRatingsCount(HasRatingInterface::DISLIKE);
    }

    public function getRatingsCount(int $rating): int
    {
        return $this->userVideoRatings->filter(fn($uvr) => $uvr->getRating() === $rating)->count();
    }

    #[Groups(['rating'])]
    public function getUserRating(User $user): ?int
    {
        $result = $this->userVideoRatings
            ->filter(fn($uvr) => $uvr->getUser()->getId() === $user->getId())
            ->first();
        
        return $result ? $result->getRating() : null;
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
    public function getUserVideoActions(): Collection
    {
        return $this->userVideoActions;
    }

    public function addUserVideoAction(UserVideoAction $userVideoAction): self
    {
        if (!$this->userVideoActions->contains($userVideoAction)) {
            $this->userVideoActions->add($userVideoAction);
            $userVideoAction->setVideo($this);
        }

        return $this;
    }

    public function removeUserVideoAction(UserVideoAction $userVideoAction): self
    {
        if ($this->userVideoActions->removeElement($userVideoAction)) {
            if ($userVideoAction->getVideo() === $this) {
                $userVideoAction->setVideo(null);
            }
        }

        return $this;
    }

    public function getUserActions(User $user): Collection|array
    {
        return $this->userVideoActions->filter(fn($uva) => $uva->getUser()->getId() === $user->getId());
    }
}
