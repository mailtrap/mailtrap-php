<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\EmailCampaign;

use Mailtrap\DTO\Request\RequestInterface;

/**
 * Class ReplyTo
 *
 * Reply-To address parts.
 */
final class ReplyTo implements RequestInterface
{
    /**
     * @param string|null $displayName Display name shown in the Reply-To header
     * @param string|null $localPart   Local part (before the @) of the Reply-To address
     * @param string|null $domain      Domain of the Reply-To address
     */
    public function __construct(
        private ?string $displayName = null,
        private ?string $localPart = null,
        private ?string $domain = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'display_name' => $this->displayName,
                'local_part' => $this->localPart,
                'domain' => $this->domain,
            ],
            fn ($value) => $value !== null
        );
    }
}
