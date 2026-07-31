<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Inbound;

use Mailtrap\DTO\Request\RequestInterface;

/**
 * Class UpdateInboundInbox
 */
final class UpdateInboundInbox implements RequestInterface
{
    public function __construct(
        private string $name,
    ) {
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
