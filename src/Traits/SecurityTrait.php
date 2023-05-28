<?php

namespace App\Traits;

use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Service\Attribute\Required;

trait SecurityTrait
{
    protected Security $security;

    #[Required]
    public function setSeecurity(Security $security): void
    {
        $this->security = $security;
    }
}