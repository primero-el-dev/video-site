<?php

namespace App\Entity\Interfaces;

use App\Entity\User;

interface HasUserInterface
{
    public function setUser(?User $user): static;

    public function getUser(): ?User;
}