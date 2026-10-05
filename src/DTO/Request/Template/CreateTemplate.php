<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Template;

use Mailtrap\DTO\Request\RequestInterface;

/**
 * Class CreateTemplate
 *
 * Attributes for creating a template. The request body is flat (no wrapper key).
 */
final class CreateTemplate implements RequestInterface
{
    /**
     * @param string      $name     Template name (required)
     * @param string      $subject  Email subject (required)
     * @param string      $category Template category (required)
     * @param string|null $bodyHtml HTML body
     * @param string|null $bodyText Plain-text body
     */
    public function __construct(
        private string $name,
        private string $subject,
        private string $category,
        private ?string $bodyHtml = null,
        private ?string $bodyText = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'name' => $this->name,
                'subject' => $this->subject,
                'category' => $this->category,
                'body_html' => $this->bodyHtml,
                'body_text' => $this->bodyText,
            ],
            fn ($value) => $value !== null
        );
    }
}
