<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\EmailLogs;

use Mailtrap\DTO\Request\RequestInterface;

/**
 * Single filter criterion for Email Logs list (operator + optional value).
 * Use for: to, from, subject, status, events, clicks_count, opens_count,
 * client_ip, sending_ip, email_service_provider_response, email_service_provider,
 * recipient_mx, category, sending_domain_id, sending_stream.
 *
 * Use EmailLogsFilterOperator for operator constants and EmailLogsFilterValue for
 * status, event type, and sending_stream values. For subject empty/not_empty use withoutValue().
 */
final class FilterCriterion implements RequestInterface
{
    public function __construct(
        private string $operator,
        private mixed $value = null
    ) {
    }

    public static function withValue(string $operator, string|int|array $value): self
    {
        return new self($operator, $value);
    }

    /** For subject empty/not_empty and similar operators that have no value. */
    public static function withoutValue(string $operator): self
    {
        return new self($operator, null);
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function toArray(): array
    {
        $result = ['operator' => $this->operator];
        if ($this->value !== null) {
            $result['value'] = $this->value;
        }

        return $result;
    }
}
