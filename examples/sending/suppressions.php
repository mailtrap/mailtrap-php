<?php

declare(strict_types=1);

use Mailtrap\Config;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapSendingClient;

require __DIR__ . '/../vendor/autoload.php';

$accountId = $_ENV['MAILTRAP_ACCOUNT_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']); // Your API token from https://mailtrap.io/api-tokens
$mailtrapSuppression = (new MailtrapSendingClient($config))->suppressions($accountId);

/**
 * Get all Suppressions.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/suppressions
 */
try {
    // The endpoint returns up to 1000 suppressions per request.
    $response = $mailtrapSuppression->getSuppressions();

    // OR get suppressions by email
    $response = $mailtrapSuppression->getSuppressions('some_email@mail.com');

    // Print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}


/**
 * Delete Suppression by ID.
 *
 * DELETE https://mailtrap.io/api/accounts/{account_id}/suppressions/{suppression_id}
 */
try {
    // Delete a suppression by ID (UUID)
    $suppressionId = '019706a8-0000-0000-0000-4f26816b467a'; // Replace with a valid suppression ID
    $response = $mailtrapSuppression->deleteSuppression($suppressionId);

    // Print the response status code
    var_dump($response->getStatusCode());
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}
