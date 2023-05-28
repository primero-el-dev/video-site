<?php

namespace App\Entity\Interfaces;

use App\Entity\Enum\UserActionEnum;

interface HasUserActionInterface
{
    public function setAction(?UserActionEnum $action): static;

    public function getAction(): ?UserActionEnum;
}