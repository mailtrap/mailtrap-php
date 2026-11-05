<?php

declare(strict_types=1);

namespace Mailtrap\Api\Sandbox;

use Mailtrap\Exception\InvalidArgumentException;
use Mailtrap\Helper\Deprecation;

/**
 * The sandbox id of a sandbox-scoped API. Constructors still accept it under its deprecated name, $inboxId.
 *
 * @internal
 */
trait SandboxIdTrait
{
    private int $sandboxId;

    public function getSandboxId(): int
    {
        return $this->sandboxId;
    }

    /**
     * @deprecated Use getSandboxId() instead.
     */
    public function getInboxId(): int
    {
        Deprecation::trigger(self::class . '::getInboxId()', self::class . '::getSandboxId()');

        return $this->sandboxId;
    }

    private function setSandboxId(?int $sandboxId, ?int $inboxId): void
    {
        $sandboxId = Deprecation::renamedArgument(
            self::class . '::__construct()',
            'inboxId',
            'sandboxId',
            $inboxId,
            $sandboxId
        );

        if (null === $sandboxId) {
            throw new InvalidArgumentException(sprintf('%s::__construct() requires the $sandboxId argument.', self::class));
        }

        $this->sandboxId = $sandboxId;
    }
}
