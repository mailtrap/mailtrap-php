<?php

use Mailtrap\Config;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapGeneralClient;

require __DIR__ . '/../vendor/autoload.php';

$accountId = (int) $_ENV['MAILTRAP_ACCOUNT_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens
$billing = (new MailtrapGeneralClient($config))->billing($accountId);


/**
 * Get current billing cycle usage for Email Testing and Email Sending.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/billing/usage
 */
try {
    $response = $billing->getBillingUsage();

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}

