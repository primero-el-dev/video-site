<?php

namespace App\Entity;

use App\Entity\Enum\UserActionEnum;
use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\HasOwnerInterface;
use App\Entity\Interfaces\HasRatingInterface;
use App\Entity\Interfaces\HasUserActionRelationInterface;
use App\Entity\Notification\NotificationForComment;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Entity\Traits\SoftDeleteTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Uid\UuidV6;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\UserSubjectAction\UserCommentAction;

#[ORM\HasLifecycleCallbacks]
#[Gedmo\SoftDeleteable]
#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment implements EntityInterface, HasOwnerInterface, HasRatingInterface, HasUserActionRelationInterface
{
    use CreatedAtTrait;
    use UpdatedAtTrait;
    use SoftDeleteTrait;

    #[Groups(['comment', 'comment_extended'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?UuidV6 $id = null;

    #[Groups(['comment', 'comment_extended'])]
    #[Assert\NotBlank(message: 'entity.comment.content.notBlank')]
    #[Assert\Length(max: 4095, maxMessage: 'entity.comment.content.length.max')]
    #[ORM\Column(type: 'text', length: 4095)]
    private ?string $content = null;

    #[Groups(['comment', 'comment_extended'])]
    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $children;

    #[Groups(['comment_extended'])]
    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Video $video = null;

    #[ORM\OneToMany(mappedBy: 'subject', targetEntity: UserCommentAction::class, orphanRemoval: true)]
    private Collection $userCommentActions;

    #[Groups(['comment', 'comment_extended'])]
    #[ORM\Column(name: '`order`', type: 'integer', nullable: true, insertable: false)]
    private ?int $order = null;

    #[ORM\OneToMany(targetEntity: NotificationForComment::class, mappedBy: 'subject', fetch: 'EXTRA_LAZY')]
    private Collection $notificationsWhereSubject;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->userCommentActions = new ArrayCollection();
        $this->notificationsWhereSubject = new ArrayCollection();
    }

    public function getId(): ?UuidV6
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

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

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    #[Groups(['comment'])]
    public function getChildrenCount(): int
    {
        return $this->children->count();
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): static
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserCommentAction>
     */
    public function getUserSubjectActions(): Collection
    {
        return $this->userCommentActions;
    }

    /**
     * @return Collection<int, UserCommentAction>
     */
    public function getUserCommentActions(): Collection
    {
        return $this->userCommentActions;
    }

    public function addUserCommentAction(UserCommentAction $userCommentAction): self
    {
        if (!$this->userCommentActions->contains($userCommentAction)) {
            $this->userCommentActions->add($userCommentAction);
            $userCommentAction->setSubject($this);
        }

        return $this;
    }

    public function removeUserCommentAction(UserCommentAction $userCommentAction): self
    {
        if ($this->userCommentActions->removeElement($userCommentAction)) {
            if ($userCommentAction->getSubject() === $this) {
                $userCommentAction->setSubject(null);
            }
        }

        return $this;
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
        return $this->userCommentActions->filter(fn($uvr) => $uvr->getAction() === $action)->count();
    }

    #[Groups(['rating'])]
    public function getUserRating(User $user): ?UserActionEnum
    {
        $result = $this->userCommentActions
            ->filter(fn($uvr) => $uvr->getUser()->getId() === $user->getId() 
                && in_array($uvr->getAction(), [UserActionEnum::LIKE, UserActionEnum::DISLIKE]))
            ->first();
        
        return $result ? $result->getAction() : null;
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

    public function getUserActions(User $user): Collection|array
    {
        $data = $this->userCommentActions
            ->filter(fn($uca) => $uca->getUser()->getId() === $user->getId())
            ->toArray();

        return array_values($data);
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    /**
     * @return Collection<int, NotificationForComment>
     */
    public function getNotificationsWhereSubject(): Collection
    {
        return $this->notificationsWhereSubject;
    }

    public function addNotificationsWhereSubject(NotificationForComment $notificationsWhereSubject): self
    {
        if (!$this->notificationsWhereSubject->contains($notificationsWhereSubject)) {
            $this->notificationsWhereSubject->add($notificationsWhereSubject);
            $notificationsWhereSubject->setSubject($this);
        }

        return $this;
    }

    public function removeNotificationsWhereSubject(NotificationForComment $notificationsWhereSubject): self
    {
        if ($this->notificationsWhereSubject->removeElement($notificationsWhereSubject)) {
            $notificationsWhereSubject->setSubject(null);
        }

        return $this;
    }
}
