<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Inbound;

use Mailtrap\DTO\Request\RequestInterface;

/**
 * Class CreateInboundInbox
 *
 * Omit `domainId` for a Mailtrap-hosted inbox; pass it to create a
 * custom-domain (catch-all) inbox.
 */
final class CreateInboundInbox implements RequestInterface
{
    public function __construct(
        private string $name,
        private ?int $domainId = null,
    ) {
    }

    public function toArray(): array
    {
        $payload = [
            'name' => $this->name,
        ];

        if ($this->domainId !== null) {
            $payload['domain_id'] = $this->domainId;
        }

        return $payload;
    }
}
