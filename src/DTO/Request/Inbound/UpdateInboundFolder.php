<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Inbound;

use Mailtrap\DTO\Request\RequestInterface;

/**
 * Class UpdateInboundFolder
 */
final class UpdateInboundFolder implements RequestInterface
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
