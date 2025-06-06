<?php
declare(strict_types=1);

namespace Blu\Foundation\Security;

use Blu\Foundation\Security\Exception\AccessDeniedException;

final class AccessControl
{
    public function __construct(private array $permissions)
    {
    }

    public function isAllowed(string $role, string $permission): bool
    {
        return in_array($permission, $this->permissions[$role] ?? [], true);
    }

    public function requirePermission(string $role, string $permission): void
    {
        if (!$this->isAllowed($role, $permission)) {
            throw new AccessDeniedException("Permission '{$permission}' denied for role '{$role}'.");
        }
    }
}
