<?php

use Mailtrap\Config;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapGeneralClient;

require __DIR__ . '/../vendor/autoload.php';

$accountId = $_ENV['MAILTRAP_ACCOUNT_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens
$generalUsers = (new MailtrapGeneralClient($config))->users($accountId);

/**
 * List all users in account
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/account_accesses
 */
try {
    $response = $generalUsers->getList();

    // OR with query parameters (not required)
    $inboxIds = [2000005, 2000006];
    $projectIds = [1005001];
    $response = $generalUsers->getList($inboxIds, $projectIds);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

/**
 * Remove user from the account
 *
 * DELETE https://mailtrap.io/api/accounts/{account_id}/account_accesses/{account_access_id}
 */
try {
    $accountAccessId = 10000009;

    $response = $generalUsers->delete($accountAccessId);

    // Print the response status code
    var_dump($response->getStatusCode());
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
