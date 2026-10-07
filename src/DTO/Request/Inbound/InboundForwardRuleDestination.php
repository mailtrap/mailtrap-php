<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Inbound;

use Mailtrap\DTO\Request\RequestInterface;

/**
 * Class InboundForwardRuleDestination
 */
final class InboundForwardRuleDestination implements RequestInterface
{
    public function __construct(
        private string $email,
    ) {
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
        ];
    }
}
