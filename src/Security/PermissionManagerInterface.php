<?php

namespace App\Security;
use App\Security\Voter\HasPermissionsInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

interface PermissionManagerInterface
{
    public function filterGranted(mixed $subject, array $permissions = []): array;
}