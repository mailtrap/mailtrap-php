<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Permission;

use Mailtrap\Exception\RuntimeException;

/**
 * Class Permissions
 */
final class Permissions
{
    /**
     * @var PermissionInterface[]
     */
    private array $permissions = [];

    public function __construct(PermissionInterface ...$permissions)
    {
        foreach ($permissions as $permission) {
            $this->add($permission);
        }
    }

    public function add(PermissionInterface $permission): Permissions
    {
        $this->permissions[] = $permission;

        return $this;
    }

    /**
     * @return PermissionInterface[]
     */
    public function getAll(): array
    {
        return $this->permissions;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toPayload(): array
    {
        $payload = array_map(static fn(PermissionInterface $p) => $p->toArray(), $this->permissions);

        if ($payload === []) {
            throw new RuntimeException('At least one "permission" object must be provided');
        }

        return $payload;
    }
}
