<?php

use Mailtrap\Config;
use Mailtrap\DTO\Request\Permission\CreateOrUpdatePermission;
use Mailtrap\DTO\Request\Permission\DestroyPermission;
use Mailtrap\DTO\Request\Permission\PermissionInterface;
use Mailtrap\DTO\Request\Permission\Permissions;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapGeneralClient;

require __DIR__ . '/../vendor/autoload.php';

$accountId = $_ENV['MAILTRAP_ACCOUNT_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens
$generalPermissions = (new MailtrapGeneralClient($config))->permissions($accountId);

/**
 * Get resources
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/permissions/resources
 */
try {
    $response = $generalPermissions->getResources();

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Manage user or token permissions
 *
 * If you send a combination of resource_type and resource_id that already exists, the permission is updated.
 * If the combination doesn’t exist, the permission is created.
 *
 * PUT https://mailtrap.io/api/accounts/{account_id}/account_accesses/{account_access_id}/permissions/bulk
 */
try {
    $accountAccessId = $_ENV['MAILTRAP_ACCOUNT_ACCESS_ID'];

    // resource IDs
    $projectResourceId = $_ENV['MAILTRAP_NEW_PROJECT_RESOURCE_ID'];
    $inboxResourceId = $_ENV['MAILTRAP_INBOX_RESOURCE_ID'];
    $destroyProjectResourceId = $_ENV['MAILTRAP_OLD_PROJECT_RESOURCE_ID'];

    $permissions = new Permissions(
        new CreateOrUpdatePermission($projectResourceId, PermissionInterface::TYPE_PROJECT, 10), // viewer = 10
        new CreateOrUpdatePermission($inboxResourceId, PermissionInterface::TYPE_INBOX, 100), // admin = 100
        new DestroyPermission($destroyProjectResourceId, PermissionInterface::TYPE_PROJECT),
    );

    $response = $generalPermissions->update($accountAccessId, $permissions);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
