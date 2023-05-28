<?php

namespace App\Entity\Traits;

use App\Entity\Enum\UserActionEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait UserActionTrait
{
    #[Groups(['user_action'])]
    #[ORM\Column(type: 'string', enumType: UserActionEnum::class, length: 70)]
    protected ?UserActionEnum $action = null;

    public function getAction(): ?UserActionEnum
    {
        return $this->action;
    }

    public function setAction(?UserActionEnum $action): static
    {
        $this->action = $action;

        return $this;
    }
}
