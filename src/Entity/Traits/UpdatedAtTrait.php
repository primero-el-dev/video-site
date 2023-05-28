<?php

namespace App\Entity\Traits;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Ignore;

trait UpdatedAtTrait
{
    #[Groups(['timestamp'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $updatedAt = null;

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    #[Ignore]
    #[ORM\PreUpdate]
    public function setUpdatedAtToNow(): self
    {
        $this->updatedAt = new DateTime();

        return $this;
    }
}