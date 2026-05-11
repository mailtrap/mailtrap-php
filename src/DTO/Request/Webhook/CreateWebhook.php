<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Webhook;

use Mailtrap\Exception\InvalidArgumentException;

/**
 * Class CreateWebhook
 */
final class CreateWebhook implements WebhookInterface
{
    /**
     * @param string      $url            Webhook destination URL
     * @param string      $webhookType    One of WebhookInterface::TYPE_*
     * @param string[]    $eventTypes     Subset of WebhookInterface::EVENT_* (required for email_sending)
     * @param string|null $payloadFormat  One of WebhookInterface::PAYLOAD_FORMAT_*
     * @param string|null $sendingStream  One of WebhookInterface::SENDING_STREAM_* (required for email_sending)
     * @param int|null    $domainId       Scope to a specific domain id (null = all account domains)
     * @param bool|null   $active         Defaults to true on the server side
     */
    public function __construct(
        private string $url,
        private string $webhookType,
        private array $eventTypes = [],
        private ?string $payloadFormat = null,
        private ?string $sendingStream = null,
        private ?int $domainId = null,
        private ?bool $active = null,
    ) {
        if (!in_array($webhookType, [self::TYPE_EMAIL_SENDING, self::TYPE_AUDIT_LOG], true)) {
            throw new InvalidArgumentException(sprintf(
                '"webhookType" must be one of "%s" or "%s", "%s" given',
                self::TYPE_EMAIL_SENDING,
                self::TYPE_AUDIT_LOG,
                $webhookType
            ));
        }

        if ($webhookType === self::TYPE_EMAIL_SENDING) {
            if ($eventTypes === []) {
                throw new InvalidArgumentException('"eventTypes" is required for email_sending webhooks');
            }
            if ($sendingStream === null) {
                throw new InvalidArgumentException('"sendingStream" is required for email_sending webhooks');
            }
        }
    }

    public function toArray(): array
    {
        $payload = [
            'url' => $this->url,
            'webhook_type' => $this->webhookType,
            'event_types' => $this->eventTypes,
        ];

        if ($this->payloadFormat !== null) {
            $payload['payload_format'] = $this->payloadFormat;
        }

        if ($this->sendingStream !== null) {
            $payload['sending_stream'] = $this->sendingStream;
        }

        if ($this->domainId !== null) {
            $payload['domain_id'] = $this->domainId;
        }

        if ($this->active !== null) {
            $payload['active'] = $this->active;
        }

        return $payload;
    }
}
