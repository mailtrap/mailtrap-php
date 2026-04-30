<?php

use Mailtrap\Config;
use Mailtrap\DTO\Request\Permission\CreateOrUpdatePermission;
use Mailtrap\DTO\Request\Permission\PermissionInterface;
use Mailtrap\DTO\Request\Permission\Permissions;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapGeneralClient;

require __DIR__ . '/../../vendor/autoload.php';

$accountId = $_ENV['MAILTRAP_ACCOUNT_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens
$apiTokens = (new MailtrapGeneralClient($config))->apiTokens($accountId);

/**
 * List all API tokens in the account.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/api_tokens
 */
try {
    $response = $apiTokens->getApiTokens();

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

/**
 * Get a single API token by ID.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/api_tokens/{id}
 */
try {
    $apiTokenId = 1;

    $response = $apiTokens->getApiToken($apiTokenId);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

/**
 * Create a new API token. The full token value is returned only in this response.
 *
 * POST https://mailtrap.io/api/accounts/{account_id}/api_tokens
 */
try {
    $permissions = new Permissions(
        new CreateOrUpdatePermission(
            resourceId: $accountId,
            resourceType: PermissionInterface::TYPE_ACCOUNT,
            accessLevel: 1000
        )
    );

    $response = $apiTokens->createApiToken('My new API token', $permissions);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

/**
 * Reset an API token by ID. Returns a new token value; the previous value stops working.
 *
 * POST https://mailtrap.io/api/accounts/{account_id}/api_tokens/{id}/reset
 */
try {
    $apiTokenId = 1;

    $response = $apiTokens->resetApiToken($apiTokenId);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

/**
 * Delete an API token by ID.
 *
 * DELETE https://mailtrap.io/api/accounts/{account_id}/api_tokens/{id}
 */
try {
    $apiTokenId = 1;

    $response = $apiTokens->deleteApiToken($apiTokenId);

    var_dump($response->getStatusCode());
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
