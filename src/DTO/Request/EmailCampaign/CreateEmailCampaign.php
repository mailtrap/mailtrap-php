<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\EmailCampaign;

/**
 * Class CreateEmailCampaign
 *
 * Attributes for creating an email campaign. The request body is flat (no wrapper key).
 * The campaign is always created in the `draft` state; scheduling and starting are
 * separate lifecycle endpoints.
 */
final class CreateEmailCampaign implements EmailCampaignInterface
{
    /**
     * @param string             $name               Campaign name (required)
     * @param int                $domainId           ID of the verified sending domain, as returned by the Sending Domains endpoints (required)
     * @param string             $fromLocalPart      Local part (before the @) of the From address (required)
     * @param TemplateAttributes $templateAttributes Template attributes; `subject` is required on create
     * @param string|null        $fromDisplayName    Display name shown in the From header
     * @param ReplyTo|null       $replyTo            Reply-To address parts
     * @param string|null        $deliveryMode       One of EmailCampaignInterface::DELIVERY_MODE_*
     * @param array<string, mixed>|null $deliveryOptions Delivery throttling options (`emails_per_hour`), applies to `gradual` mode
     * @param int[]|null         $contactListIds     IDs of contact lists to send to (the full set — `[]` clears it)
     * @param int[]|null         $contactSegmentIds  IDs of contact segments to send to (the full set — `[]` clears it)
     */
    public function __construct(
        private string $name,
        private int $domainId,
        private string $fromLocalPart,
        private TemplateAttributes $templateAttributes,
        private ?string $fromDisplayName = null,
        private ?ReplyTo $replyTo = null,
        private ?string $deliveryMode = null,
        private ?array $deliveryOptions = null,
        private ?array $contactListIds = null,
        private ?array $contactSegmentIds = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'name' => $this->name,
                'domain_id' => $this->domainId,
                'from_local_part' => $this->fromLocalPart,
                'from_display_name' => $this->fromDisplayName,
                'reply_to' => $this->replyTo?->toArray(),
                'template_attributes' => $this->templateAttributes->toArray(),
                'delivery_mode' => $this->deliveryMode,
                'delivery_options' => $this->deliveryOptions,
                'contact_list_ids' => $this->contactListIds,
                'contact_segment_ids' => $this->contactSegmentIds,
            ],
            fn ($value) => $value !== null
        );
    }
}
