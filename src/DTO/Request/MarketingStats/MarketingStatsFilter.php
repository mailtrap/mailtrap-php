<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\MarketingStats;

use Mailtrap\Exception\InvalidArgumentException;

/**
 * Class MarketingStatsFilter
 *
 * Query filters for the aggregated Email Marketing stats. The date range is required;
 * every other filter is optional and left out of the query when empty.
 */
final class MarketingStatsFilter implements MarketingStatsInterface
{
    /**
     * @param string   $startDate             Start of the date range (inclusive), `YYYY-MM-DD`
     * @param string   $endDate               End of the date range (inclusive), `YYYY-MM-DD`
     * @param int[]    $sendingDomainIds      Only traffic sent from these sending domains
     * @param string[] $sources               Subset of MarketingStatsInterface::SOURCES; one value narrows
     *                                        to that source, none or both mean all Email Marketing traffic
     * @param string[] $emailServiceProviders Only traffic to these email service providers (e.g. "Google")
     */
    public function __construct(
        private string $startDate,
        private string $endDate,
        private array $sendingDomainIds = [],
        private array $sources = [],
        private array $emailServiceProviders = [],
    ) {
        foreach ($sources as $source) {
            if (!in_array($source, self::SOURCES, true)) {
                throw new InvalidArgumentException(sprintf(
                    '"sources" must contain only "%s", "%s" given',
                    implode('", "', self::SOURCES),
                    $source
                ));
            }
        }
    }

    public function toArray(): array
    {
        $payload = [
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ];

        if ($this->sendingDomainIds !== []) {
            $payload['sending_domain_ids'] = $this->sendingDomainIds;
        }

        if ($this->sources !== []) {
            $payload['sources'] = $this->sources;
        }

        if ($this->emailServiceProviders !== []) {
            $payload['email_service_providers'] = $this->emailServiceProviders;
        }

        return $payload;
    }
}
