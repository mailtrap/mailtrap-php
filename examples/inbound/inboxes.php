<?php

use Mailtrap\Config;
use Mailtrap\DTO\Request\Inbound\CreateInboundInbox;
use Mailtrap\DTO\Request\Inbound\UpdateInboundInbox;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapInboundClient;

require __DIR__ . '/../../vendor/autoload.php';

$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens

$folderId = (int) ($_ENV['MAILTRAP_INBOUND_FOLDER_ID'] ?? 0);
$inboxId = (int) ($_ENV['MAILTRAP_INBOUND_INBOX_ID'] ?? 0);

// Inboxes are scoped to a folder.
$inboxes = (new MailtrapInboundClient($config))->inboxes($folderId);

/**
 * List inboxes in the folder.
 *
 * GET https://mailtrap.io/api/inbound/folders/{folder_id}/inboxes
 */
try {
    $response = $inboxes->getList();

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

/**
 * Create an inbox. Omit the domain id for a Mailtrap-hosted inbox; pass it to
 * create a custom-domain (catch-all) inbox.
 *
 * POST https://mailtrap.io/api/inbound/folders/{folder_id}/inboxes
 */
try {
    $response = $inboxes->create(
        new CreateInboundInbox('Tickets', $_ENV['MAILTRAP_INBOUND_DOMAIN_ID'] ?? null)
    );

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

/**
 * Get an inbox by ID.
 *
 * GET https://mailtrap.io/api/inbound/folders/{folder_id}/inboxes/{inbox_id}
 */
try {
    $response = $inboxes->getById($inboxId);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

/**
 * Rename an inbox.
 *
 * PATCH https://mailtrap.io/api/inbound/folders/{folder_id}/inboxes/{inbox_id}
 */
try {
    $response = $inboxes->update($inboxId, new UpdateInboundInbox('Tickets (renamed)'));

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

/**
 * Delete an inbox.
 *
 * DELETE https://mailtrap.io/api/inbound/folders/{folder_id}/inboxes/{inbox_id}
 */
try {
    $response = $inboxes->delete($inboxId);

    var_dump($response->getStatusCode());
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}
