<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\EmailCampaign;

use Mailtrap\Exception\RuntimeException;

/**
 * Class UpdateEmailCampaign
 *
 * Attributes for updating a `draft` email campaign. The request body is flat (no wrapper key).
 * The update is a PATCH: all fields are optional and only provided fields are sent.
 */
final class UpdateEmailCampaign implements EmailCampaignInterface
{
    /**
     * @param string|null             $name               Campaign name
     * @param int|null                $domainId           ID of the verified sending domain, as returned by the Sending Domains endpoints
     * @param string|null             $fromDisplayName    Display name shown in the From header
     * @param string|null             $fromLocalPart      Local part (before the @) of the From address
     * @param string|null             $deliveryMode       One of EmailCampaignInterface::DELIVERY_MODE_*
     * @param array<string, mixed>|null $deliveryOptions  Delivery throttling options (`emails_per_hour`), applies to `gradual` mode
     * @param ReplyTo|null            $replyTo            Reply-To address parts
     * @param TemplateAttributes|null $templateAttributes Template attributes; edited in place, partial
     * @param int[]|null              $contactListIds     IDs of contact lists to send to (the full set — `[]` clears it)
     * @param int[]|null              $contactSegmentIds  IDs of contact segments to send to (the full set — `[]` clears it)
     */
    public function __construct(
        private ?string $name = null,
        private ?int $domainId = null,
        private ?string $fromDisplayName = null,
        private ?string $fromLocalPart = null,
        private ?string $deliveryMode = null,
        private ?array $deliveryOptions = null,
        private ?ReplyTo $replyTo = null,
        private ?TemplateAttributes $templateAttributes = null,
        private ?array $contactListIds = null,
        private ?array $contactSegmentIds = null,
    ) {
    }

    public function toArray(): array
    {
        $payload = array_filter(
            [
                'name' => $this->name,
                'domain_id' => $this->domainId,
                'from_display_name' => $this->fromDisplayName,
                'from_local_part' => $this->fromLocalPart,
                'delivery_mode' => $this->deliveryMode,
                'delivery_options' => $this->deliveryOptions,
                'reply_to' => $this->replyTo?->toArray(),
                'template_attributes' => $this->templateAttributes?->toArray(),
                'contact_list_ids' => $this->contactListIds,
                'contact_segment_ids' => $this->contactSegmentIds,
            ],
            fn ($value) => $value !== null
        );

        if ($payload === []) {
            throw new RuntimeException('At least one attribute must be provided to update an email campaign.');
        }

        return $payload;
    }
}
