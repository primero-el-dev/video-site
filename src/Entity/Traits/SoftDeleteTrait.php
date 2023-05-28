<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;

trait SoftDeleteTrait
{
    #[Ignore]
    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?DateTime $deletedAt = null;

    public function setDeletedAt(DateTime $deletedAt = null): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    #[Ignore]
    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    #[Groups(['is_deleted'])]
    public function isDeleted(): bool
    {
        return (bool) $this->deletedAt;
    }
}