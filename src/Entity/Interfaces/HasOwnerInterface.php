<?php

namespace App\Entity\Interfaces;

use App\Entity\User;

interface HasOwnerInterface
{
    public function setOwner(?User $user): static;

    public function getOwner(): ?User;
}