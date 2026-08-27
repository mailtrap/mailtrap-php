<?php

declare(strict_types=1);

use Mailtrap\Config;
use Mailtrap\DTO\Request\TrackingOptOut\CreateTrackingOptOut;
use Mailtrap\DTO\Request\TrackingOptOut\TrackingOptOutsFilter;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapSendingClient;

require __DIR__ . '/../../vendor/autoload.php';

$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens
$domainId = (int) $_ENV['MAILTRAP_DOMAIN_ID'];

$trackingOptOuts = (new MailtrapSendingClient($config))->trackingOptOuts();

/**
 * Create a tracking opt-out
 *
 * POST https://mailtrap.io/api/tracking_opt_outs
 */
try {
    $response = $trackingOptOuts->createTrackingOptOut(
        new CreateTrackingOptOut(email: 'tracked@example.com', domainId: $domainId)
    );

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Get tracking opt-outs
 *
 * GET https://mailtrap.io/api/tracking_opt_outs
 *
 * Returns up to 1000 records per request. When `last_id` is not null, pass it
 * back as a filter to fetch the next page.
 */
try {
    $response = $trackingOptOuts->getTrackingOptOuts();

    // OR filter by email and creation time
    $response = $trackingOptOuts->getTrackingOptOuts(
        new TrackingOptOutsFilter(
            email: 'tracked@example.com',
            startTime: '2025-01-01T00:00:00Z',
            endTime: '2025-12-31T23:59:59Z'
        )
    );

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Delete a tracking opt-out
 *
 * DELETE https://mailtrap.io/api/tracking_opt_outs/{tracking_opt_out_id}
 */
try {
    $response = $trackingOptOuts->deleteTrackingOptOut('64d71bf3-1276-417b-86e1-8e66f138acfe');

    // print the response body (array) — the deleted record
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
