<?php

namespace App\Entity;

use App\Entity\UserView\UserVideoView;
use App\Entity\Interfaces\EntityInterface;
use App\Entity\Notification\Notification;
use App\Entity\Notification\NotificationForUser;
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
use App\Entity\UserSubjectAction\UserCommentAction;
use App\Entity\UserSubjectAction\UserVideoAction;
use App\Entity\UserSubjectAction\UserUserAction;

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

    #[Groups(['user_videos'])]
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

    #[Groups(['user_comments'])]
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Comment::class)]
    private Collection $comments;

    #[Groups(['user_extended', 'user_full'])]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserVideoAction::class, orphanRemoval: true)]
    private Collection $userVideoActions;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserCommentAction::class, orphanRemoval: true)]
    private Collection $userCommentActions;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserVideoView::class, fetch: 'EXTRA_LAZY')]
    private Collection $userVideoViews;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserUserAction::class, orphanRemoval: true)]
    private Collection $userActionsOnAnothers;

    #[ORM\OneToMany(mappedBy: 'subject', targetEntity: UserUserAction::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private Collection $otherUsersActions;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Notification::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private Collection $notifications;

    #[ORM\OneToMany(targetEntity: NotificationForUser::class, mappedBy: 'subject', fetch: 'EXTRA_LAZY')]
    private Collection $notificationsWhereSubject;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Playlist::class)]
    private Collection $playlists;

    public function __construct()
    {
        $this->tokens = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->userVideoActions = new ArrayCollection();
        $this->userVideoViews = new ArrayCollection();
        $this->userActionsOnAnothers = new ArrayCollection();
        $this->otherUsersActions = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->notificationsWhereSubject = new ArrayCollection();
        $this->playlists = new ArrayCollection();
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
            $userVideoView->setUser($this);
        }

        return $this;
    }

    public function removeUserVideoView(UserVideoView $userVideoView): self
    {
        if ($this->userVideoViews->removeElement($userVideoView)) {
            if ($userVideoView->getUser() === $this) {
                $userVideoView->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserUserAction>
     */
    public function getUserActionsOnAnothers(): Collection
    {
        return $this->userActionsOnAnothers;
    }

    public function addUserActionsOnAnother(UserUserAction $userActionsOnAnother): self
    {
        if (!$this->userActionsOnAnothers->contains($userActionsOnAnother)) {
            $this->userActionsOnAnothers->add($userActionsOnAnother);
            $userActionsOnAnother->setUser($this);
        }

        return $this;
    }

    public function removeUserActionsOnAnothers(UserUserAction $userActionsOnAnother): self
    {
        if ($this->userActionsOnAnothers->removeElement($userActionsOnAnother)) {
            if ($userActionsOnAnother->getUser() === $this) {
                $userActionsOnAnother->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserUserAction>
     */
    public function getUserSubjectActions(): Collection
    {
        return $this->otherUsersActions;
    }
    
    /**
     * @return Collection<int, UserUserAction>
     */
    public function getOtherUsersActions(): Collection
    {
        return $this->otherUsersActions;
    }

    public function addOtherUsersAction(UserUserAction $otherUsersAction): self
    {
        if (!$this->otherUsersActions->contains($otherUsersAction)) {
            $this->otherUsersActions->add($otherUsersAction);
            $otherUsersAction->setSubject($this);
        }

        return $this;
    }

    public function removeOtherUsersAction(UserUserAction $otherUsersAction): self
    {
        if ($this->otherUsersActions->removeElement($otherUsersAction)) {
            if ($otherUsersAction->getSubject() === $this) {
                $otherUsersAction->setSubject(null);
            }
        }

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
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, NotificationForUser>
     */
    public function getNotificationsWhereSubject(): Collection
    {
        return $this->notificationsWhereSubject;
    }

    public function addNotificationsWhereSubject(NotificationForUser $notificationsWhereSubject): self
    {
        if (!$this->notificationsWhereSubject->contains($notificationsWhereSubject)) {
            $this->notificationsWhereSubject->add($notificationsWhereSubject);
            $notificationsWhereSubject->setSubject($this);
        }

        return $this;
    }

    public function removeNotificationsWhereSubject(NotificationForUser $notificationsWhereSubject): self
    {
        if ($this->notificationsWhereSubject->removeElement($notificationsWhereSubject)) {
            $notificationsWhereSubject->setSubject(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, Playlist>
     */
    public function getPlaylists(): Collection
    {
        return $this->playlists;
    }

    public function addPlaylist(Playlist $playlist): self
    {
        if (!$this->playlists->contains($playlist)) {
            $this->playlists->add($playlist);
            $playlist->setOwner($this);
        }

        return $this;
    }

    public function removePlaylist(Playlist $playlist): self
    {
        if ($this->playlists->removeElement($playlist)) {
            // set the owning side to null (unless already changed)
            if ($playlist->getOwner() === $this) {
                $playlist->setOwner(null);
            }
        }

        return $this;
    }
}
