<?php
# app/routes/console.php
# php artisan send-sandbox-mail

use Illuminate\Support\Facades\Artisan;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

Artisan::command('send-sandbox-mail', function () {
    $email = (new MailtrapEmail())
        ->from(new Address('hello@example.com', 'Mailtrap Sandbox'))
        ->to(new Address("email@gmail.com"))
        ->subject('You are awesome!')
        ->text('Congrats for sending test email with Mailtrap!')
    ;

    $response = MailtrapClient::initSendingEmails(
        apiKey:    $_ENV['MAILTRAP_API_KEY'], // your API token from here https://mailtrap.io/api-tokens
        isSandbox: true,
        inboxId:   $_ENV['MAILTRAP_INBOX_ID'], // your Inbox ID from here https://mailtrap.io/inboxes
    )->send($email);

    var_dump(ResponseHelper::toArray($response));
})->purpose('Send Sandbox Mail');
