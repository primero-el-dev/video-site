<?php

namespace App\Entity;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\HasOwnerInterface;
use App\Entity\Interfaces\HasRatingRelationInterface;
use App\Entity\Interfaces\HasRatingInterface;
use App\Entity\Interfaces\HasUserActionRelationInterface;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Entity\Traits\SoftDeleteTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\HasLifecycleCallbacks]
#[Gedmo\SoftDeleteable]
#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment implements EntityInterface, HasOwnerInterface, HasRatingRelationInterface, HasUserActionRelationInterface
{
    use CreatedAtTrait;
    use UpdatedAtTrait;
    use SoftDeleteTrait;
    
    #[Groups(['comment', 'comment_extended'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

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

    #[ORM\OneToMany(mappedBy: 'comment', targetEntity: UserCommentRating::class, orphanRemoval: true)]
    private Collection $userCommentRatings;

    #[Groups(['comment_extended'])]
    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Video $video = null;

    #[ORM\OneToMany(mappedBy: 'comment', targetEntity: UserCommentAction::class, orphanRemoval: true)]
    private Collection $userCommentActions;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->userCommentRatings = new ArrayCollection();
        $this->userCommentActions = new ArrayCollection();
    }

    public function getId(): ?int
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
     * @return Collection<int, UserCommentRating>
     */
    public function getUserCommentRatings(): Collection
    {
        return $this->userCommentRatings;
    }

    public function addUserCommentRating(UserCommentRating $userCommentRating): self
    {
        if (!$this->userCommentRatings->contains($userCommentRating)) {
            $this->userCommentRatings->add($userCommentRating);
            $userCommentRating->setComment($this);
        }

        return $this;
    }

    public function removeUserCommentRating(UserCommentRating $userCommentRating): self
    {
        if ($this->userCommentRatings->removeElement($userCommentRating)) {
            if ($userCommentRating->getComment() === $this) {
                $userCommentRating->setComment(null);
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
        return $this->userCommentRatings->filter(fn($ucr) => $ucr->getRating() === $rating)->count();
    }

    #[Groups(['rating'])]
    public function getUserRating(User $user): ?int
    {
        $result = $this->userCommentRatings
            ->filter(fn($ucr) => $ucr->getUser()->getId() === $user->getId())
            ->first();
        
        return $result ? $result->getRating() : null;
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
        return $this->userCommentActions->filter(fn($uca) => $uca->getUser()->getId() === $user->getId());
    }
}
