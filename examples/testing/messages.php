<?php

use Mailtrap\Config;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapSandboxClient;

require __DIR__ . '/../vendor/autoload.php';

// your API token from here https://mailtrap.io/api-tokens
$accountId = $_ENV['MAILTRAP_ACCOUNT_ID'];
$inboxId = $_ENV['MAILTRAP_INBOX_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens

$sandboxMessages = (new MailtrapSandboxClient($config))->messages($accountId, $inboxId); #required parameters are accountId and inboxId

/**
 * Get messages
 * Note: if you want to get all messages you need to use "page" param (by default will return only 30 messages per page)
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}/messages
 */
try {
    // not required parameters
    $page = 1; // by default  30 messages per page
    $search = 'hello'; // it works like case insensitive pattern matching by subject, to_email, to_name
    $lastMessageId = 3000000003; // get emails, where primary key is less then this param (does not work with page param)

    $response = $sandboxMessages->getList();

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Show email message
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}/messages/{message_id}
 */
try {
    $messageId = $_ENV['MAILTRAP_INBOX_MESSAGE_ID'];

    $response = $sandboxMessages->getById($messageId);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Get message source
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}/messages/{message_id}/body.htmlsource
 */
try {
    $messageId = $_ENV['MAILTRAP_INBOX_MESSAGE_ID'];

    $response = $sandboxMessages->getSource($messageId);

    // print the response body (string)
    var_dump(ResponseHelper::toString($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Get message as .eml
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}/messages/{message_id}/body.eml
 */
try {
    $messageId = $_ENV['MAILTRAP_INBOX_MESSAGE_ID'];

    $response = $sandboxMessages->getEml($messageId);

    // print the response body (string)
    var_dump(ResponseHelper::toString($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Get HTML message
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}/messages/{message_id}/body.html
 */
try {
    $messageId = $_ENV['MAILTRAP_INBOX_MESSAGE_ID'];

    $response = $sandboxMessages->getHtml($messageId);

    // print the response body (string)
    var_dump(ResponseHelper::toString($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Get raw message
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}/messages/{message_id}/body.raw
 */
try {
    $messageId = $_ENV['MAILTRAP_INBOX_MESSAGE_ID'];

    $response = $sandboxMessages->getRaw($messageId);

    // print the response body (string)
    var_dump(ResponseHelper::toString($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Get text message
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}/messages/{message_id}/body.txt
 */
try {
    $messageId = $_ENV['MAILTRAP_INBOX_MESSAGE_ID'];

    $response = $sandboxMessages->getText($messageId);

    // print the response body (string)
    var_dump(ResponseHelper::toString($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Get message HTML analysis
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}/messages/{message_id}/analyze
 */
try {
    $messageId = $_ENV['MAILTRAP_INBOX_MESSAGE_ID'];

    $response = $sandboxMessages->getHtmlAnalysis($messageId);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Get message spam score
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}/messages/{message_id}/spam_report
 */
try {
    $messageId = $_ENV['MAILTRAP_INBOX_MESSAGE_ID'];

    $response = $sandboxMessages->getSpamScore($messageId);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Update message (mark as read)
 *
 * PATCH https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}/messages/{message_id}
 */
try {
    $messageId = $_ENV['MAILTRAP_INBOX_MESSAGE_ID'];

    $response = $sandboxMessages->markAsRead($messageId);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Delete message
 *
 * DELETE https://mailtrap.io/api/accounts/{account_id}/inboxes/{inbox_id}/messages/{message_id}
 */
try {
    $messageId = $_ENV['MAILTRAP_INBOX_MESSAGE_ID'];

    $response = $sandboxMessages->delete($messageId);

    // Print the response status code
    var_dump($response->getStatusCode());
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
