<?php
# app/routes/console.php
# php artisan send-template-mail

use Illuminate\Support\Facades\Artisan;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

Artisan::command('send-template-mail', function () {
    $email = (new MailtrapEmail())
        ->from(new Address('example@YOUR-DOMAIN-HERE.com', 'Mailtrap Template')) // <--- you should use your domain here that you installed in the mailtrap.io admin area (otherwise you will get 401)
        ->replyTo(new Address('reply@YOUR-DOMAIN-HERE.com'))
        ->to(new Address('example@gmail.com', 'Jon'))
        // when using a template, you should not set a subject, text, HTML, category
        // otherwise there will be a validation error from the API side
        ->templateUuid('bfa432fd-0000-0000-0000-8493da283a69')
        ->templateVariables([
            'user_name' => 'Jon Bush',
            'next_step_link' => 'https://mailtrap.io/',
            'get_started_link' => 'https://mailtrap.io/',
            'onboarding_video_link' => 'some_video_link',
            'company' => [
                'name' => 'Best Company',
                'address' => 'Its Address',
            ],
            'products' => [
                [
                    'name' => 'Product 1',
                    'price' => 100,
                ],
                [
                    'name' => 'Product 2',
                    'price' => 200,
                ],
            ],
            'isBool' => true,
            'int' => 123
        ])
    ;

    $response = MailtrapClient::initSendingEmails(
        apiKey: $_ENV['MAILTRAP_API_KEY'] // your API token from here https://mailtrap.io/api-tokens
    )->send($email);

    var_dump(ResponseHelper::toArray($response));
})->purpose('Send Template Mail');
