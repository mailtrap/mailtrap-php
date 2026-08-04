<?php

use Mailtrap\Config;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapInboundClient;

require __DIR__ . '/../../vendor/autoload.php';

$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens

$inboxId = (int) ($_ENV['MAILTRAP_INBOUND_INBOX_ID'] ?? 0);
$threadId = $_ENV['MAILTRAP_INBOUND_THREAD_ID'] ?? '';

// Threads are scoped to an inbox.
$threads = (new MailtrapInboundClient($config))->threads($inboxId);

/**
 * List conversation threads. Pass the previous page's last id to paginate.
 *
 * GET https://mailtrap.io/api/inbound/inboxes/{inbox_id}/threads
 */
try {
    $response = $threads->getList();

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

/**
 * Get a single thread with its messages embedded.
 *
 * GET https://mailtrap.io/api/inbound/inboxes/{inbox_id}/threads/{thread_id}
 */
try {
    $response = $threads->getById($threadId);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

/**
 * Delete a thread.
 *
 * DELETE https://mailtrap.io/api/inbound/inboxes/{inbox_id}/threads/{thread_id}
 */
try {
    $response = $threads->delete($threadId);

    var_dump($response->getStatusCode());
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}
