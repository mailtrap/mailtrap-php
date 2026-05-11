<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Webhook;

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
     * @param string|null   $payloadFormat One of WebhookInterface::PAYLOAD_FORMAT_*
     * @param string[]|null $eventTypes    Subset of WebhookInterface::EVENT_*
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

        return $payload;
    }
}
