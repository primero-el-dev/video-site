<?php

namespace App\Entity\Traits;

use App\Entity\AdditionalData;
use Symfony\Component\Serializer\Annotation\Groups;

trait AdditionalDataTrait
{
    #[Groups(['additional_info'])]
    protected ?AdditionalData $additionalData = null;

    public function getAdditionalData(): ?AdditionalData
    {
        return $this->additionalData;
    }

    public function setAdditionalData(?AdditionalData $additionalData = null): static
    {
        $this->additionalData = $additionalData;

        return $this;
    }
}
