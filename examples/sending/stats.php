<?php

use Mailtrap\Config;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapSendingClient;

require __DIR__ . '/../../vendor/autoload.php';

$accountId = (int) $_ENV['MAILTRAP_ACCOUNT_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens
$stats = (new MailtrapSendingClient($config))->stats($accountId);

/**
 * Get aggregated sending stats.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/stats
 */
try {
    $response = $stats->get('2026-01-01', '2026-01-31');

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}

/**
 * Get aggregated sending stats with filters.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/stats
 */
try {
    $response = $stats->get(
        '2026-01-01',
        '2026-01-31',
        sendingDomainIds: [1, 2],
        sendingStreams: ['transactional'],
        categories: ['Transactional', 'Marketing'],
        emailServiceProviders: ['Gmail', 'Yahoo']
    );

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}

/**
 * Get sending stats grouped by domains.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/stats/domains
 */
try {
    $response = $stats->byDomain('2026-01-01', '2026-01-31');

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}

/**
 * Get sending stats grouped by categories.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/stats/categories
 */
try {
    $response = $stats->byCategory('2026-01-01', '2026-01-31');

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}

/**
 * Get sending stats grouped by email service providers.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/stats/email_service_providers
 */
try {
    $response = $stats->byEmailServiceProvider('2026-01-01', '2026-01-31');

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}

/**
 * Get sending stats grouped by date.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/stats/date
 */
try {
    $response = $stats->byDate('2026-01-01', '2026-01-31');

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}
