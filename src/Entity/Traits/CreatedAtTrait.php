<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;

trait CreatedAtTrait
{
    #[Groups(['timestamp'])]
    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[Ignore]
    #[ORM\PrePersist]
    public function setCreatedAtToNow(): self
    {
        $this->createdAt = new DateTimeImmutable();

        return $this;
    }
}