<?php

use Mailtrap\Config;
use Mailtrap\DTO\Request\Inbox;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapSandboxClient;

require __DIR__ . '/../vendor/autoload.php';

$accountId = $_ENV['MAILTRAP_ACCOUNT_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens

$sandboxInboxes = (new MailtrapSandboxClient($config))->inboxes($accountId); #required parameter is accountId

/**
 * Get a list of inboxes.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/inboxes
 */
try {
    $response = $sandboxInboxes->getList();

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Create an inbox
 *
 * POST https://mailtrap.io/api/accounts/{account_id}/projects/{project_id}/inboxes
 */
try {
    $projectId = $_ENV['MAILTRAP_PROJECT_ID'];
    $inboxName = 'First inbox';

    $response = $sandboxInboxes->create($projectId, $inboxName);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Get inbox attributes
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}
 */
try {
    $inboxId = $_ENV['MAILTRAP_INBOX_ID'];

    $response = $sandboxInboxes->getInboxAttributes($inboxId);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Delete an inbox
 *
 * DELETE https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}
 */
try {
    $inboxId = $_ENV['MAILTRAP_INBOX_ID'];

    $response = $sandboxInboxes->delete($inboxId);

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
    $inboxId = $_ENV['MAILTRAP_INBOX_ID'];

    $response = $sandboxInboxes->resetEmailAddress($inboxId);

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
    $inboxId = $_ENV['MAILTRAP_INBOX_ID'];

    $response = $sandboxInboxes->toggleEmailAddress($inboxId);

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
    $inboxId = $_ENV['MAILTRAP_INBOX_ID'];

    $response = $sandboxInboxes->resetSmtpCredentials($inboxId);

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
    $inboxId = $_ENV['MAILTRAP_INBOX_ID'];

    $response = $sandboxInboxes->markAsRead($inboxId);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Clean inbox
 *
 * PATCH https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}/clean
 */
try {
    $inboxId = $_ENV['MAILTRAP_INBOX_ID'];

    $response = $sandboxInboxes->clean($inboxId);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Update an inbox
 *
 * PATCH https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}
 */
try {
    $inboxId = $_ENV['MAILTRAP_INBOX_ID'];
    $newInboxName = 'New inbox name';
    $newEmailUsername = 'new-email-username';

    $response = $sandboxInboxes->update(
        $inboxId,
        new Inbox($newInboxName, $newEmailUsername)
    );

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
