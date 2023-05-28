<?php

namespace App\Entity\UserSubjectAction;

use App\Entity\AdditionalData;
use App\Entity\Enum\UserActionEnum;
use App\Entity\Notification\Notification;
use App\Entity\User;
use App\Entity\Video;
use App\Entity\Comment;
use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\HasAdditionalDataInterface;
use App\Entity\Interfaces\HasUserActionInterface;
use App\Entity\Traits\UserActionTrait;
use App\Repository\UserSubjectAction\UserSubjectActionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'subject_type', type: 'string')]
#[ORM\DiscriminatorMap(UserSubjectAction::DISCRIMINATOR_MAP)]
#[ORM\Entity(repositoryClass: UserSubjectActionRepository::class)]
abstract class UserSubjectAction implements EntityInterface, HasUserActionInterface, HasAdditionalDataInterface
{
    use UserActionTrait;
    
    public const DISCRIMINATOR_MAP = [
        Comment::class => UserCommentAction::class,
        Video::class => UserVideoAction::class,
        User::class => UserUserAction::class,
    ];

    #[Ignore]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[Ignore]
    #[ORM\JoinColumn(nullable: false)]
    protected ?User $user = null;

    #[Groups(['additional_data'])]
    #[ORM\OneToOne(targetEntity: AdditionalData::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'additional_data_id', referencedColumnName: 'id')]
    protected ?AdditionalData $additionalData = null;

    #[ORM\ManyToMany(targetEntity: Notification::class, mappedBy: 'userSubjectActions', fetch: 'EXTRA_LAZY')]
    private Collection $notifications;

    /**
     * True means that if we have already this action
     * in database and try to insert it, it will be deleted.
     */
    #[Ignore]
    public static function isActionToggleable(UserActionEnum $action): bool
    {
        return in_array($action, [UserActionEnum::LIKE, UserActionEnum::DISLIKE]);
    }

    /**
     * Get actions conflicting with current. 
     * This is used application logic to delete other actions on save.
     * 
     * @return self[]
     */
    public static function getConflictingActions(UserActionEnum $action, bool $withCurrent = false): array
    {
        $conflicting = match($action) {
            UserActionEnum::LIKE => [UserActionEnum::DISLIKE],
            UserActionEnum::DISLIKE => [UserActionEnum::LIKE],
            default => [],
        };

        if ($withCurrent) {
            $conflicting[] = $action;
        }

        return $conflicting;
    }

    public static function hasAdditionalData(UserActionEnum $action): bool
    {
        return in_array($action, [UserActionEnum::REPORT]);
    }

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
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

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getAdditionalData(): ?AdditionalData
    {
        return $this->additionalData;
    }

    public function setAdditionalData(?AdditionalData $additionalData = null): static
    {
        $this->additionalData = $additionalData;

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->addUserSubjectAction($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            $notification->removeUserSubjectAction($this);
        }

        return $this;
    }
}
