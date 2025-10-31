<?php

use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

require __DIR__ . '/../vendor/autoload.php';

/**
 * Email Bulk Sending WITH TEMPLATE
 *
 * WARNING! If a template is provided, then subject, text, html, category and other params are forbidden.
 *
 * UUID of email template. Subject, text and html will be generated from template using optional template_variables.
 * Optional template variables that will be used to generate actual subject, text and html from email template
 */
try {
    $bulkMailtrap = MailtrapClient::initSendingEmails(
        apiKey: $_ENV['MAILTRAP_API_KEY'], #your API token from here https://mailtrap.io/api-tokens
        isBulk: true # Bulk sending (@see https://help.mailtrap.io/article/113-sending-streams)
    );

    $email = (new MailtrapEmail())
        ->from(new Address('example@YOUR-DOMAIN-HERE.com', 'Mailtrap Test')) // <--- you should use your domain here that you installed in the mailtrap.io admin area (otherwise you will get 401)
        ->replyTo(new Address('reply@YOUR-DOMAIN-HERE.com'))
        ->to(new Address('example@gmail.com', 'Jon'))
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

    $response = $bulkMailtrap->send($email);

    var_dump(ResponseHelper::toArray($response)); // body (array)
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
