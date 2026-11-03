<?php

use Mailtrap\Config;
use Mailtrap\DTO\Request\Sandbox;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapSandboxClient;

require __DIR__ . '/../../vendor/autoload.php';

$accountId = $_ENV['MAILTRAP_ACCOUNT_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens

$sandboxes = (new MailtrapSandboxClient($config))->sandboxes($accountId); #required parameter is accountId

/**
 * Get a list of sandboxes.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/inboxes
 */
try {
    $response = $sandboxes->getList();

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Create a sandbox
 *
 * POST https://mailtrap.io/api/accounts/{account_id}/projects/{project_id}/inboxes
 */
try {
    $projectId = $_ENV['MAILTRAP_PROJECT_ID'];
    $sandboxName = 'First sandbox';

    $response = $sandboxes->create($projectId, $sandboxName);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Get sandbox attributes
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}
 */
try {
    $sandboxId = $_ENV['MAILTRAP_SANDBOX_ID'];

    $response = $sandboxes->getSandboxAttributes($sandboxId);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Delete a sandbox
 *
 * DELETE https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}
 */
try {
    $sandboxId = $_ENV['MAILTRAP_SANDBOX_ID'];

    $response = $sandboxes->delete($sandboxId);

    // Print the response status code
    var_dump($response->getStatusCode());
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Reset email address
 *
 * PATCH https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}/reset_email_username
 */
try {
    $sandboxId = $_ENV['MAILTRAP_SANDBOX_ID'];

    $response = $sandboxes->resetEmailAddress($sandboxId);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Enable/Disable email address
 *
 * PATCH https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}/toggle_email_username
 */
try {
    $sandboxId = $_ENV['MAILTRAP_SANDBOX_ID'];

    $response = $sandboxes->toggleEmailAddress($sandboxId);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Reset credentials
 *
 * PATCH https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}/reset_credentials
 */
try {
    $sandboxId = $_ENV['MAILTRAP_SANDBOX_ID'];

    $response = $sandboxes->resetSmtpCredentials($sandboxId);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Mark as read
 *
 * PATCH https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}/all_read
 */
try {
    $sandboxId = $_ENV['MAILTRAP_SANDBOX_ID'];

    $response = $sandboxes->markAsRead($sandboxId);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Clean sandbox
 *
 * PATCH https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}/clean
 */
try {
    $sandboxId = $_ENV['MAILTRAP_SANDBOX_ID'];

    $response = $sandboxes->clean($sandboxId);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Update a sandbox
 *
 * PATCH https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}
 */
try {
    $sandboxId = $_ENV['MAILTRAP_SANDBOX_ID'];
    $newSandboxName = 'New sandbox name';
    $newEmailUsername = 'new-email-username';

    $response = $sandboxes->update(
        $sandboxId,
        new Sandbox($newSandboxName, $newEmailUsername)
    );

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
