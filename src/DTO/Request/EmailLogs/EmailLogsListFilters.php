<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\EmailLogs;

use Mailtrap\DTO\Request\RequestInterface;

/**
 * Email Logs list filters (date range + named criteria).
 * Use with EmailLogs::getList(). Criteria keys: to, from, subject, status, events,
 * clicks_count, opens_count, client_ip, sending_ip, email_service_provider_response,
 * email_service_provider, recipient_mx, category, sending_domain_id, sending_stream.
 * Use EmailLogsFilterOperator and EmailLogsFilterValue when building FilterCriterion instances.
 */
final class EmailLogsListFilters implements RequestInterface
{
    private const VALID_CRITERIA_KEYS = [
        'to',
        'from',
        'subject',
        'status',
        'events',
        'clicks_count',
        'opens_count',
        'client_ip',
        'sending_ip',
        'email_service_provider_response',
        'email_service_provider',
        'recipient_mx',
        'category',
        'sending_domain_id',
        'sending_stream',
    ];

    /**
     * @param string|null $sentAfter ISO 8601 datetime (start of sent-at range)
     * @param string|null $sentBefore ISO 8601 datetime (end of sent-at range)
     * @param array<string, FilterCriterion> $criteria Map of filter name => FilterCriterion (e.g. 'to' => FilterCriterion::withValue('ci_equal', 'a@b.com'))
     */
    public function __construct(
        private ?string $sentAfter = null,
        private ?string $sentBefore = null,
        private array $criteria = []
    ) {
    }

    public static function create(
        ?string $sentAfter = null,
        ?string $sentBefore = null,
        array $criteria = []
    ): self {
        return new self($sentAfter, $sentBefore, $criteria);
    }

    public function withSentAfter(string $iso8601): self
    {
        return new self($iso8601, $this->sentBefore, $this->criteria);
    }

    public function withSentBefore(string $iso8601): self
    {
        return new self($this->sentAfter, $iso8601, $this->criteria);
    }

    public function withCriterion(string $name, FilterCriterion $criterion): self
    {
        $criteria = $this->criteria;
        $criteria[$name] = $criterion;

        return new self($this->sentAfter, $this->sentBefore, $criteria);
    }

    public function toArray(): array
    {
        $result = [];
        if ($this->sentAfter !== null && $this->sentAfter !== '') {
            $result['sent_after'] = $this->sentAfter;
        }
        if ($this->sentBefore !== null && $this->sentBefore !== '') {
            $result['sent_before'] = $this->sentBefore;
        }
        foreach ($this->criteria as $name => $criterion) {
            if (\in_array($name, self::VALID_CRITERIA_KEYS, true) && $criterion instanceof FilterCriterion) {
                $result[$name] = $criterion->toArray();
            }
        }

        return $result;
    }
}
