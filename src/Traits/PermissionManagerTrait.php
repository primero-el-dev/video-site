<?php

namespace App\Traits;

use App\Security\PermissionManagerInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait PermissionManagerTrait
{
    protected PermissionManagerInterface $permissionManager;

    #[Required]
    public function setPermissionManager(PermissionManagerInterface $permissionManager): void
    {
        $this->permissionManager = $permissionManager;
    }
}