<?php

declare(strict_types=1);

use Mailtrap\Config;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapSendingClient;

require __DIR__ . '/../../vendor/autoload.php';

$accountId = (int) $_ENV['MAILTRAP_ACCOUNT_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens

$sendingDomains = (new MailtrapSendingClient($config))->domains($accountId); #required parameter is accountId

/**
 * Get a list of sending domains.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/domains
 */
try {
    $response = $sendingDomains->getSendingDomains();

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Create a new sending domain
 *
 * POST https://mailtrap.io/api/accounts/{account_id}/domains
 */
try {
    $domainName = 'example.com';

    $response = $sendingDomains->createSendingDomain($domainName);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Get a sending domain by ID
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/domains/{domain_id}
 */
try {
    $domainId = (int) $_ENV['MAILTRAP_DOMAIN_ID']; // Set this environment variable with a valid domain ID

    $response = $sendingDomains->getDomainById($domainId);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Send domain setup instructions
 *
 * POST https://mailtrap.io/api/accounts/{account_id}/domains/{domain_id}/send_setup_instructions
 */
try {
    $domainId = (int) $_ENV['MAILTRAP_DOMAIN_ID']; // Set this environment variable with a valid domain ID
    $email = 'devops@example.com'; // Email address to send setup instructions to

    $response = $sendingDomains->sendDomainSetupInstructions($domainId, $email);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Delete a sending domain
 *
 * DELETE https://mailtrap.io/api/accounts/{account_id}/domains/{domain_id}
 */
try {
    $domainId = (int) $_ENV['MAILTRAP_DOMAIN_ID']; // Set this environment variable with a valid domain ID

    $response = $sendingDomains->deleteSendingDomain($domainId);

    // Print the response status code
    var_dump($response->getStatusCode());
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
