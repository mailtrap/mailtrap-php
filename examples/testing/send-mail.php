<?php

use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\UnstructuredHeader;

require __DIR__ . '/../vendor/autoload.php';


/**
 * Email Testing API
 *
 * POST https://sandbox.api.mailtrap.io/api/send/{inbox_id}
 */
try {
    $mailtrap = MailtrapClient::initSendingEmails(
        apiKey: $_ENV['MAILTRAP_API_KEY'], #your API token from here https://mailtrap.io/api-tokens
        isSandbox: true, # Sandbox sending (@see https://help.mailtrap.io/article/109-getting-started-with-mailtrap-email-testing)
        inboxId: $_ENV['MAILTRAP_INBOX_ID'] # required param for sandbox sending
    );

    $email = (new MailtrapEmail())
        ->from(new Address('mailtrap@example.com', 'Mailtrap Test'))
        ->to(new Address('email@example.com', 'Jon'))
        ->subject('Hello from Mailtrap!')
        ->text('Welcome to Mailtrap Sandbox!')
    ;

    $response = $mailtrap->send($email);

    // print information from the response
    var_dump(ResponseHelper::toArray($response)); // body (array)
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
