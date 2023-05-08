<?php

namespace App\Entity\Traits;

use App\Entity\Enum\UserActionEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait UserActionTrait
{
    #[Groups(['user_action'])]
    #[ORM\Column(type: 'string', enumType: UserActionEnum::class, length: 70)]
    private ?UserActionEnum $action = null;

    /**
     * @param array<string, mixed> $additionalInfo
     */
    #[Groups(['user_action'])]
    #[ORM\Column(type: 'json')]
    private array $additionalInfo = [];

    public function getAction(): ?UserActionEnum
    {
        return $this->action;
    }

    public function setAction(?UserActionEnum $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getAdditionalInfo(): array
    {
        return $this->additionalInfo;
    }

    public function setAdditionalInfo(array $additionalInfo): static
    {
        $this->additionalInfo = $additionalInfo;

        return $this;
    }
}
