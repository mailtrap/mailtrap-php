<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\EmailCampaign;

use Mailtrap\DTO\Request\RequestInterface;

/**
 * Class TemplateAttributes
 *
 * Inline email template — the campaign's subject and design. The template is always edited
 * in place: only the provided sub-fields change, omitted sub-fields keep their current value.
 */
final class TemplateAttributes implements RequestInterface
{
    /**
     * @param string|null   $subject   Email subject line (required when creating a campaign). Supports `{{tag_name}}` merge tags.
     * @param string|null   $bodyHtml  HTML body (the design). Required before the campaign can be scheduled or started.
     *                                 Include an unsubscribe link via the `__unsubscribe_url__` placeholder.
     * @param string|null   $bodyText  Optional plain-text alternative of the email body
     * @param string[]|null $mergeTags Bare names of the merge tags referenced in the subject/body (without `{{ }}`).
     *                                 Replaced as a whole when provided.
     */
    public function __construct(
        private ?string $subject = null,
        private ?string $bodyHtml = null,
        private ?string $bodyText = null,
        private ?array $mergeTags = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'subject' => $this->subject,
                'body_html' => $this->bodyHtml,
                'body_text' => $this->bodyText,
                'merge_tags' => $this->mergeTags,
            ],
            fn ($value) => $value !== null
        );
    }
}
