<?php

namespace App\Entity\Interfaces;

use App\Entity\AdditionalData;

interface HasAdditionalDataInterface
{
    public function setAdditionalData(?AdditionalData $additionalData): static;

    public function getAdditionalData(): ?AdditionalData;
}