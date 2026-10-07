<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Template;

use Mailtrap\DTO\Request\RequestInterface;
use Mailtrap\Exception\RuntimeException;

/**
 * Class UpdateTemplate
 *
 * Attributes for updating a template. The request body is flat (no wrapper key).
 * The update is a PATCH: all fields are optional and only provided fields are sent.
 * Pass an empty string to clear a body; null leaves the field unchanged.
 * The API accepts an empty PATCH as a no-op, but toArray() throws instead, like UpdateEmailCampaign.
 */
final class UpdateTemplate implements RequestInterface
{
    /**
     * @param string|null $name     Template name
     * @param string|null $subject  Email subject
     * @param string|null $category Template category
     * @param string|null $bodyHtml HTML body ('' clears it)
     * @param string|null $bodyText Plain-text body ('' clears it)
     */
    public function __construct(
        private ?string $name = null,
        private ?string $subject = null,
        private ?string $category = null,
        private ?string $bodyHtml = null,
        private ?string $bodyText = null,
    ) {
    }

    public function toArray(): array
    {
        $payload = array_filter(
            [
                'name' => $this->name,
                'subject' => $this->subject,
                'category' => $this->category,
                'body_html' => $this->bodyHtml,
                'body_text' => $this->bodyText,
            ],
            fn ($value) => $value !== null
        );

        if ($payload === []) {
            throw new RuntimeException('At least one attribute must be provided to update a template.');
        }

        return $payload;
    }
}
