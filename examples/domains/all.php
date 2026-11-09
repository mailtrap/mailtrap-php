<?php

declare(strict_types=1);

use Mailtrap\Config;
use Mailtrap\DTO\Request\Domain\UpdateDomain;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapSendingClient;

require __DIR__ . '/../../vendor/autoload.php';

$accountId = (int) $_ENV['MAILTRAP_ACCOUNT_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens

$domains = (new MailtrapSendingClient($config))->domains($accountId); #required parameter is accountId

/**
 * Get a list of domains.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/domains
 */
try {
    $response = $domains->getDomains();

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Create a new domain
 *
 * POST https://mailtrap.io/api/accounts/{account_id}/domains
 */
try {
    $domainName = 'example.com';

    $response = $domains->createDomain($domainName);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Get a domain by ID
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/domains/{domain_id}
 */
try {
    $domainId = (int) $_ENV['MAILTRAP_DOMAIN_ID']; // Set this environment variable with a valid domain ID

    $response = $domains->getDomainById($domainId);

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

    $response = $domains->sendDomainSetupInstructions($domainId, $email);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Update domain configuration settings
 *
 * PATCH https://mailtrap.io/api/accounts/{account_id}/domains/{domain_id}
 */
try {
    $domainId = (int) $_ENV['MAILTRAP_DOMAIN_ID']; // Set this environment variable with a valid domain ID

    $response = $domains->updateDomain(
        $domainId,
        new UpdateDomain(
            openTrackingEnabled: true,
            clickTrackingEnabled: true,
            trackingOptOutEnabled: true,
            autoUnsubscribeLinkEnabled: false,
            inboundEnabled: false
        )
    );

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Delete a domain
 *
 * DELETE https://mailtrap.io/api/accounts/{account_id}/domains/{domain_id}
 */
try {
    $domainId = (int) $_ENV['MAILTRAP_DOMAIN_ID']; // Set this environment variable with a valid domain ID

    $response = $domains->deleteDomain($domainId);

    // Print the response status code
    var_dump($response->getStatusCode());
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
