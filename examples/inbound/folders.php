<?php

use Mailtrap\Config;
use Mailtrap\DTO\Request\Inbound\CreateInboundFolder;
use Mailtrap\DTO\Request\Inbound\UpdateInboundFolder;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapInboundClient;

require __DIR__ . '/../../vendor/autoload.php';

$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens
$folders = (new MailtrapInboundClient($config))->folders();

$folderId = (int) ($_ENV['MAILTRAP_INBOUND_FOLDER_ID'] ?? 0);

/**
 * List all inbound folders.
 *
 * GET https://mailtrap.io/api/inbound/folders
 */
try {
    $response = $folders->getList();

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

/**
 * Create an inbound folder.
 *
 * POST https://mailtrap.io/api/inbound/folders
 */
try {
    $response = $folders->create(new CreateInboundFolder('Support'));

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

/**
 * Get an inbound folder by ID.
 *
 * GET https://mailtrap.io/api/inbound/folders/{folder_id}
 */
try {
    $response = $folders->getById($folderId);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

/**
 * Rename an inbound folder.
 *
 * PATCH https://mailtrap.io/api/inbound/folders/{folder_id}
 */
try {
    $response = $folders->update($folderId, new UpdateInboundFolder('Support (renamed)'));

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

/**
 * Delete an inbound folder (removes the folder and all of its inboxes).
 *
 * DELETE https://mailtrap.io/api/inbound/folders/{folder_id}
 */
try {
    $response = $folders->delete($folderId);

    var_dump($response->getStatusCode());
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}
