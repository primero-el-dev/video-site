<?php

namespace App\Security;

use App\Security\PermissionManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class PermissionManager implements PermissionManagerInterface
{
    public function __construct(private Security $security) {}

    public function filterGranted(mixed $subject, array $permissions = []): array
    {
        return array_filter($permissions, fn($p) => $this->security->isGranted($p, $subject));
    }
}