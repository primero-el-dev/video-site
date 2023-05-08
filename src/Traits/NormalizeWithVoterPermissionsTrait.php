<?php

namespace App\Traits;

use App\Security\Voter\HasPermissionsInterface;
use App\Traits\PermissionManagerTrait;
use App\Traits\SerializerTrait;

trait NormalizeWithVoterPermissionsTrait
{
    use PermissionManagerTrait;
    use SerializerTrait;

    /**
     * @param (HasPermissionsInterface|HasPermissionsInterface[]) $voters
     * @param array<string, [string, mixed[]]> $additionalCalls - [serializedParam => [method, [args]]]
     * @return array<string, mixed>|array<string, mixed>[] with 'permissions' key 
     */
    private function normalizeWithVoterPermissions(
        mixed $subject, 
        iterable|HasPermissionsInterface $voters, 
        array $context = [],
        array $additionalCalls = [],
    ): array {
        if (!is_iterable($voters)) {
            $voters = [$voters];
        }
        
        $permissions = [];
        foreach ($voters as $voter) {
            if ($voter instanceof HasPermissionsInterface) {
                $permissions += $voter::PERMISSIONS;
            }
        }
        $permissions = array_unique($permissions);

        $data = $this->serializer->normalize($subject, 'json', $context);

        if (is_array($subject)) {
            $count = count($data);
            for ($i = 0; $i < $count; $i++) {
                $data[$i]['permissions'] = $this->permissionManager->filterGranted($subject[$i], $permissions);

                foreach ($additionalCalls as $param => [$method, $args]) {
                    $data[$i][$param] = $subject[$i]->$method(...$args);
                }
            }
        } else {
            $data['permissions'] = $this->permissionManager->filterGranted($subject, $permissions);
            
            foreach ($additionalCalls as $param => [$method, $args]) {
                $data[$param] = $subject->$method(...$args);
            }
        }

        return $data;
    }
}