<?php

namespace App\Entity;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Token\Token;
use App\Repository\UserRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Uid\UuidV7;

#[UniqueEntity(
    fields: ['email', 'deletedAt'], 
    message: 'entity.user.uniqueEntity.email', 
    errorPath: 'email', 
    ignoreNull: false,
)]
#[UniqueEntity(
    fields: ['nick', 'deletedAt'], 
    message: 'entity.user.uniqueEntity.nick', 
    errorPath: 'nick', 
    ignoreNull: false,
)]
#[Gedmo\SoftDeleteable]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements EntityInterface, UserInterface, PasswordAuthenticatedUserInterface
{
    use SoftDeleteableEntity;

    #[Groups(['user', 'user_extended', 'user_full'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?UuidV7 $id = null;

    #[Groups(['user_full'])]
    #[Assert\NotBlank(message: 'entity.user.email.notBlank')]
    #[Assert\Length(max: 180, maxMessage: 'entity.user.email.length.max')]
    #[Assert\Email(message: 'entity.user.email.email')]
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[Groups(['user_extended', 'user_full'])]
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[Ignore]
    #[ORM\Column]
    private ?string $password = null;

    #[Groups(['user_full'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false], nullable: false)]
    private bool $verified = false;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Token::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private Collection $tokens;

    #[Groups(['user_extended', 'user_full'])]
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Video::class, fetch: 'EXTRA_LAZY')]
    private Collection $videos;

    #[Groups(['user', 'user_extended', 'user_full'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagePath = null;

    #[Groups(['user_extended', 'user_full'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $backgroundPath = null;

    #[Ignore]
    #[Assert\NotBlank(message: 'entity.user.birthDate.notBlank')]
    #[Assert\Type('datetime', message: 'entity.user.birthDate.type')]
    #[Assert\LessThan('-5 years', message: 'entity.user.birthDate.lessThan')]
    #[Assert\GreaterThan('-120 years', message: 'entity.user.birthDate.greaterThan')]
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $birthDate = null;

    #[Groups(['user', 'user_extended', 'user_full'])]
    #[Assert\NotBlank(message: 'entity.user.nick.notBlank')]
    #[Assert\Length(
        min: 3, 
        max: 25, 
        minMessage: 'entity.user.nick.length.min', 
        maxMessage: 'entity.user.nick.length.max',
    )]
    #[Assert\Regex(pattern: '/^[\w\d_\-\+\. ]+$/', message: 'entity.user.nick.regex')]
    #[ORM\Column(length: 255)]
    private ?string $nick = null;

    #[Groups(['user_video_ratings'])]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserVideoRating::class)]
    private Collection $userVideoRatings;

    #[Groups(['user_comments'])]
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Comment::class)]
    private Collection $comments;

    #[Groups(['user_comment_ratings'])]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserCommentRating::class, orphanRemoval: true)]
    private Collection $userCommentRatings;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserVideoAction::class, orphanRemoval: true)]
    private Collection $userVideoActions;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserCommentAction::class, orphanRemoval: true)]
    private Collection $userCommentActions;

    public function __construct()
    {
        $this->tokens = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->userVideoRatings = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->userCommentRatings = new ArrayCollection();
        $this->userVideoActions = new ArrayCollection();
    }

    public function getId(): ?UuidV7
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    #[Ignore]
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * @param string[] $roles
     */
    #[Ignore]
    public function hasOneOfRoles(array $roles): bool
    {
        return (bool) array_intersect($this->getRoles(), $roles);
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials() {}

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): self
    {
        $this->verified = $verified;

        return $this;
    }

    /**
     * @return Collection<int, Token>
     */
    public function getTokens(): Collection
    {
        return $this->tokens;
    }

    public function addToken(Token $token): self
    {
        if (!$this->tokens->contains($token)) {
            $this->tokens->add($token);
            $token->setOwner($this);
        }

        return $this;
    }

    public function removeToken(Token $token): self
    {
        if ($this->tokens->removeElement($token)) {
            if ($token->getOwner() === $this) {
                $token->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Video>
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
            $video->setOwner($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): self
    {
        if ($this->videos->removeElement($video)) {
            if ($video->getOwner() === $this) {
                $video->setOwner(null);
            }
        }

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): self
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function getBackgroundPath(): ?string
    {
        return $this->backgroundPath;
    }

    public function setBackgroundPath(?string $backgroundPath): self
    {
        $this->backgroundPath = $backgroundPath;

        return $this;
    }

    public function getBirthDate(): ?DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getNick(): ?string
    {
        return $this->nick;
    }

    public function setNick(string $nick): self
    {
        $this->nick = $nick;

        return $this;
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
            $userVideoRating->setUser($this);
        }

        return $this;
    }

    public function removeUserVideoRating(UserVideoRating $userVideoRating): self
    {
        if ($this->userVideoRatings->removeElement($userVideoRating)) {
            if ($userVideoRating->getUser() === $this) {
                $userVideoRating->setUser(null);
            }
        }

        return $this;
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
            $comment->setOwner($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getOwner() === $this) {
                $comment->setOwner(null);
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
            $userCommentRating->setUser($this);
        }

        return $this;
    }

    public function removeUserCommentRating(UserCommentRating $userCommentRating): self
    {
        if ($this->userCommentRatings->removeElement($userCommentRating)) {
            if ($userCommentRating->getUser() === $this) {
                $userCommentRating->setUser(null);
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
            $userVideoAction->setUser($this);
        }

        return $this;
    }

    public function removeUserVideoAction(UserVideoAction $userVideoAction): self
    {
        if ($this->userVideoActions->removeElement($userVideoAction)) {
            if ($userVideoAction->getUser() === $this) {
                $userVideoAction->setUser(null);
            }
        }

        return $this;
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
            $userCommentAction->setUser($this);
        }

        return $this;
    }

    public function removeUserCommentAction(UserCommentAction $userCommentAction): self
    {
        if ($this->userCommentActions->removeElement($userCommentAction)) {
            if ($userCommentAction->getUser() === $this) {
                $userCommentAction->setUser(null);
            }
        }

        return $this;
    }
}
