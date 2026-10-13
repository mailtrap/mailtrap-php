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
                    '"sources" must contain only "%s"; "%s" given',
                    implode('", "', self::SOURCES),
                    $source
                ));
            }
        }
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'sending_domain_ids' => array_values($this->sendingDomainIds),
                'sources' => array_values($this->sources),
                'email_service_providers' => array_values($this->emailServiceProviders),
            ],
            fn ($value) => $value !== []
        );
    }
}
