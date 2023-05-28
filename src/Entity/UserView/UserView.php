<?php

namespace App\Entity\UserView;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\UserView\UserVideoView;
use App\Entity\Video;
use App\Repository\UserView\UserViewRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'subject_type', type: 'string')]
#[ORM\DiscriminatorMap(UserView::DISCRIMINATOR_MAP)]
#[ORM\Entity(repositoryClass: UserViewRepository::class)]
abstract class UserView implements EntityInterface
{
    public const DISCRIMINATOR_MAP = [
        Video::class => UserVideoView::class,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 30, nullable: true)]
    protected ?string $ip = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }
}
