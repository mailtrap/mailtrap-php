<?php

declare(strict_types=1);

use Mailtrap\Config;
use Mailtrap\DTO\Request\MarketingStats\MarketingStatsFilter;
use Mailtrap\DTO\Request\MarketingStats\MarketingStatsInterface;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapGeneralClient;

require __DIR__ . '/../../vendor/autoload.php';

$config = new Config($_ENV['MAILTRAP_API_KEY']); // your API token from https://mailtrap.io/api-tokens
$marketingStats = (new MailtrapGeneralClient($config))->marketingStats();

// The last 30 days
$today = new DateTimeImmutable();
$startDate = $today->modify('-30 days')->format('Y-m-d');
$endDate = $today->format('Y-m-d');

/**
 * Get aggregated Email Marketing stats (campaigns and automations), wrapped in `data`.
 * Delivery and bounce rates are relative to `sent_count`; open, click, spam and
 * unsubscription rates to `delivery_count`.
 *
 * GET https://mailtrap.io/api/marketing/stats
 */
try {
    $response = $marketingStats->getMarketingStats(
        new MarketingStatsFilter(startDate: $startDate, endDate: $endDate)
    );

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}

/**
 * Get Email Marketing stats with filters: campaigns only, sent to Google and Yahoo.
 * Pass `sendingDomainIds` as well to narrow the stats to specific sending domains.
 *
 * GET https://mailtrap.io/api/marketing/stats
 */
try {
    $response = $marketingStats->getMarketingStats(
        new MarketingStatsFilter(
            startDate: $startDate,
            endDate: $endDate,
            sources: [MarketingStatsInterface::SOURCE_CAMPAIGNS],
            emailServiceProviders: ['Google', 'Yahoo'],
        )
    );

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}
