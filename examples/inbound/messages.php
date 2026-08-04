<?php

use Mailtrap\Config;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapInboundClient;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

require __DIR__ . '/../../vendor/autoload.php';

$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens

$inboxId = (int) ($_ENV['MAILTRAP_INBOUND_INBOX_ID'] ?? 0);
$messageId = $_ENV['MAILTRAP_INBOUND_MESSAGE_ID'] ?? '';

// Messages are scoped to an inbox.
$messages = (new MailtrapInboundClient($config))->messages($inboxId);

/**
 * List received messages. Pass the previous page's last id to paginate.
 *
 * GET https://mailtrap.io/api/inbound/inboxes/{inbox_id}/messages
 */
try {
    $response = $messages->getList();

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

/**
 * Get a single message with its body and attachment download URLs.
 *
 * GET https://mailtrap.io/api/inbound/inboxes/{inbox_id}/messages/{message_id}
 */
try {
    $response = $messages->getById($messageId);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

/**
 * Reply to a message (sends a real email to the original sender).
 * reply/replyAll/forward accept a Symfony Mime Email, just like the send API.
 *
 * POST https://mailtrap.io/api/inbound/inboxes/{inbox_id}/messages/{message_id}/reply
 */
try {
    $email = (new Email())
        ->text('Thanks for reaching out!')
        ->html('<p>Thanks for reaching out!</p>');

    $response = $messages->reply($messageId, $email);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

/**
 * Reply to a message and copy the original's other recipients.
 *
 * POST https://mailtrap.io/api/inbound/inboxes/{inbox_id}/messages/{message_id}/reply_all
 */
try {
    $email = (new Email())->text('Looping everyone in.');

    $response = $messages->replyAll($messageId, $email);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

/**
 * Forward a message to new recipients (at least one is required).
 *
 * POST https://mailtrap.io/api/inbound/inboxes/{inbox_id}/messages/{message_id}/forward
 */
try {
    $email = (new Email())
        ->to(new Address('colleague@example.com'))
        ->text('Please take a look.');

    $response = $messages->forward($messageId, $email);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

/**
 * Delete a message.
 *
 * DELETE https://mailtrap.io/api/inbound/inboxes/{inbox_id}/messages/{message_id}
 */
try {
    $response = $messages->delete($messageId);

    var_dump($response->getStatusCode());
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}
