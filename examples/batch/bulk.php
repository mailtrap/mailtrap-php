<?php

use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

require __DIR__ . '/../vendor/autoload.php';

/**
 * Email Batch Sending API (Bulk)
 *
 * Batch send email (text, html, text&html, templates).
 *
 * Please note that the endpoint will return a 200-level http status, even when sending for individual messages may fail.
 * Users of this endpoint should check the success and errors for each message in the response (the results are ordered the same as the original messages - requests).
 * Please note that the endpoint accepts up to 500 messages per API call, and up to 50 MB payload size, including attachments.
 *
 * For this example, you need to have ready-to-use sending domain or, a Demo domain that allows sending emails to your own account email.
 * @see https://help.mailtrap.io/article/69-sending-domain-setup
 */
try {
     $mailtrap = MailtrapClient::initSendingEmails(
        apiKey: $_ENV['MAILTRAP_API_KEY'], // Your API token from https://mailtrap.io/api-tokens
        isBulk: true // Enable bulk sending
    );

    $baseEmail = (new MailtrapEmail())
        ->from(new Address('example@YOUR-DOMAIN-HERE.com', 'Mailtrap Test')) // Use your domain installed in Mailtrap
        ->subject('Batch Email Subject')
        ->text('Batch email text')
        ->html('<p>Batch email text</p>');

    $recipientEmails = [
        (new MailtrapEmail())->to(new Address('recipient1@example.com', 'Recipient 1')),
        (new MailtrapEmail())->to(new Address('recipient2@example.com', 'Recipient 2')),
    ];

    $response = $mailtrap->batchSend($recipientEmails, $baseEmail);

    var_dump(ResponseHelper::toArray($response)); // Output response body as array
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}
