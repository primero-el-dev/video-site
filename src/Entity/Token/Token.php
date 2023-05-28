<?php

namespace App\Entity\Token;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\HasOwnerInterface;
use App\Entity\User;
use App\Repository\Token\TokenRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Token\RegistrationConfirmationToken;
use App\Entity\Token\ResetPasswordToken;
use Stringable;

#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'registration_confirmation' => RegistrationConfirmationToken::class,
    'reset_password' => ResetPasswordToken::class,
])]
#[ORM\Entity(repositoryClass: TokenRepository::class)]
class Token implements EntityInterface, Stringable, HasOwnerInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 255)]
    protected ?string $value = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected ?DateTimeInterface $expiry = null;

    #[ORM\ManyToOne(inversedBy: 'tokens')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?User $owner = null;

    public function __toString()
    {
        return $this->value;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getExpiry(): ?DateTimeInterface
    {
        return $this->expiry;
    }

    public function setExpiry(DateTimeInterface $expiry): self
    {
        $this->expiry = $expiry;

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
}
