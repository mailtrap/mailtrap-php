<?php
# app/routes/console.php
# php artisan send-mail

use Illuminate\Support\Facades\Artisan;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

Artisan::command('send-mail', function () {
    $email = (new MailtrapEmail())
        ->from(new Address('hello@example.com', 'Mailtrap Transactional'))
        ->to(new Address("email@gmail.com"))
        ->subject('You are awesome!')
        ->text('Congrats for sending email with Mailtrap!')
    ;

    $response = MailtrapClient::initSendingEmails(
        apiKey: $_ENV['MAILTRAP_API_KEY'] // your API token from here https://mailtrap.io/api-tokens
    )->send($email);

    var_dump(ResponseHelper::toArray($response));
})->purpose('Send Mail');
