<?php

namespace App\Entity\Notification;

use App\Entity\Comment;
use App\Entity\Interfaces\EntityInterface;
use App\Entity\Notification\NotificationForComment;
use App\Entity\Notification\NotificationForPlaylist;
use App\Entity\Notification\NotificationForUser;
use App\Entity\Notification\NotificationForVideo;
use App\Entity\Playlist;
use App\Entity\Traits\UpdatedAtTrait;
use App\Entity\Traits\UserActionTrait;
use App\Entity\User;
use App\Entity\UserSubjectAction\UserSubjectAction;
use App\Entity\Video;
use App\Repository\Notification\NotificationRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\HasLifecycleCallbacks]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'subject_type', type: 'string')]
#[ORM\DiscriminatorMap(Notification::DISCRIMINATOR_MAP)]
#[ORM\Entity(repositoryClass: NotificationRepository::class)]
abstract class Notification implements EntityInterface
{
    use UserActionTrait;
    use UpdatedAtTrait;
    
    public const SUBJECT_TYPE = null;

    public const DISCRIMINATOR_MAP = [
        Comment::class => NotificationForComment::class,
        Video::class => NotificationForVideo::class,
        User::class => NotificationForUser::class,
        Playlist::class => NotificationForPlaylist::class,
    ];

    #[Ignore]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: true)]
    protected ?User $user = null;

    #[Groups(['notification'])]
    #[ORM\Column(nullable: false)]
    protected ?int $count = null;

    #[Groups(['notification'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    protected bool $seen = false;

    #[Groups(['notification_actions'])]
    #[ORM\ManyToMany(targetEntity: UserSubjectAction::class, inversedBy: 'notifications')]
    protected Collection $userSubjectActions;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $role = null;

    public function __construct()
    {
        $this->userSubjectActions = new ArrayCollection();
    }

    public static function getSubclassByEntity(object $entity): ?string
    {
        foreach (self::DISCRIMINATOR_MAP as $key => $subclass) {
            if (is_a($entity, $key)) {
                return $subclass;
            }
        }

        return null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }

    public function isSeen(): ?bool
    {
        return $this->seen;
    }

    public function setSeen(bool $seen): self
    {
        $this->seen = $seen;

        return $this;
    }

    #[Ignore]
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedAtToNow(): static
    {
        $this->updatedAt = new DateTime();

        return $this;
    }

    public function setUserSubjectActions(Collection $userSubjectActions): static
    {
        $this->userSubjectActions = $userSubjectActions;

        return $this;
    }

    /**
     * @return Collection<int, UserSubjectAction>
     */
    public function getUserSubjectActions(): Collection
    {
        return $this->userSubjectActions;
    }

    public function addUserSubjectAction(UserSubjectAction $userSubjectAction): self
    {
        if (!$this->userSubjectActions->contains($userSubjectAction)) {
            $this->userSubjectActions->add($userSubjectAction);
        }

        return $this;
    }

    public function removeUserSubjectAction(UserSubjectAction $userSubjectAction): self
    {
        $this->userSubjectActions->removeElement($userSubjectAction);

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;

        return $this;
    }

    #[Groups(['notification'])]
    public function getSubjectType(): ?string
    {
        return static::SUBJECT_TYPE;
    }
}
