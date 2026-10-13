<?php

declare(strict_types=1);

namespace Mailtrap\Api\General;

use Mailtrap\Api\AbstractApi;
use Mailtrap\DTO\Request\MarketingStats\MarketingStatsFilter;
use Psr\Http\Message\ResponseInterface;

/**
 * Class MarketingStats
 *
 * Aggregated Email Marketing stats (campaigns and automations). This resource is
 * token-scoped: the account is resolved from the API token, so it takes no account id.
 */
class MarketingStats extends AbstractApi implements GeneralInterface
{
    /**
     * Get aggregated Email Marketing stats for a date range, wrapped in `data`: sent,
     * delivery, bounce, open, click, spam and unsubscription counts and rates.
     * Delivery and bounce rates are relative to sent_count; open, click, spam and
     * unsubscription rates to delivery_count. Every count and rate is 0 when nothing matched.
     *
     * @param MarketingStatsFilter $filter
     * @return ResponseInterface
     */
    public function getMarketingStats(MarketingStatsFilter $filter): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath(), $filter->toArray())
        );
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/marketing/stats', $this->getHost());
    }
}
