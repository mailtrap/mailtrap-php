<?php

use Mailtrap\Config;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapGeneralClient;

require __DIR__ . '/../../vendor/autoload.php';

$organizationId = $_ENV['MAILTRAP_ORGANIZATION_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens
$subAccounts = (new MailtrapGeneralClient($config))->organization($organizationId)->subAccounts();

/**
 * List all sub-accounts in the organization.
 *
 * GET https://mailtrap.io/api/organizations/{organization_id}/sub_accounts
 */
try {
    $response = $subAccounts->getSubAccounts();

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

/**
 * Create a new sub-account in the organization.
 *
 * POST https://mailtrap.io/api/organizations/{organization_id}/sub_accounts
 */
try {
    $response = $subAccounts->createSubAccount('My new sub-account');

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
