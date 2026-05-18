<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Webhook;

use Mailtrap\Exception\InvalidArgumentException;

/**
 * Class UpdateWebhook
 *
 * Only `url`, `active`, `payload_format`, and `event_types` can be updated after creation.
 * `webhook_type`, `sending_stream`, and `domain_id` are immutable.
 */
final class UpdateWebhook implements WebhookInterface
{
    /**
     * @param string|null   $url
     * @param bool|null     $active
     * @param string|null   $payloadFormat One of Webhook::PAYLOAD_FORMAT_*
     * @param string[]|null $eventTypes    Subset of Webhook::EVENT_*. Replaces the current
     *                                     event_types list entirely (server-side replacement,
     *                                     not merge). Pass the full desired set.
     */
    public function __construct(
        private ?string $url = null,
        private ?bool $active = null,
        private ?string $payloadFormat = null,
        private ?array $eventTypes = null,
    ) {
    }

    public function toArray(): array
    {
        $payload = [];

        if ($this->url !== null) {
            $payload['url'] = $this->url;
        }

        if ($this->active !== null) {
            $payload['active'] = $this->active;
        }

        if ($this->payloadFormat !== null) {
            $payload['payload_format'] = $this->payloadFormat;
        }

        if ($this->eventTypes !== null) {
            $payload['event_types'] = $this->eventTypes;
        }

        if ($payload === []) {
            throw new InvalidArgumentException('At least one updatable field must be provided to update a webhook');
        }

        return $payload;
    }
}
