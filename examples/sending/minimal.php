<?php

use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

require __DIR__ . '/../vendor/autoload.php';


/**
 * Email Sending API
 *
 * POST https://send.api.mailtrap.io/api/send
 */
try {
    $mailtrap = MailtrapClient::initSendingEmails(
        apiKey: $_ENV['MAILTRAP_API_KEY'] #your API token from here https://mailtrap.io/api-tokens
    );

    $email = (new MailtrapEmail())
        ->from(new Address('example@YOUR-DOMAIN-HERE.com', 'Mailtrap Test')) // <--- you should use your domain here that you installed in the mailtrap.io admin area (otherwise you will get 401)
        ->to(new Address('email@example.com', 'Jon'))
        ->subject('Hello from Mailtrap!')
        ->text('Welcome to Mailtrap Sending!')
    ;

    $response = $mailtrap->send($email);

    var_dump(ResponseHelper::toArray($response)); // body (array)
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
