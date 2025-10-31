<?php

use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

require __DIR__ . '/../vendor/autoload.php';

/**
 * Email Batch Sending WITH TEMPLATE (Transactional)
 *
 * WARNING! If a template is provided, then subject, text, html, category and other params are forbidden.
 *
 * UUID of email template. Subject, text and html will be generated from template using optional template_variables.
 * Optional template variables that will be used to generate actual subject, text and html from email template
 *
 * For this example, you need to have ready-to-use sending domain or, a Demo domain that allows sending emails to your own account email.
 * @see https://help.mailtrap.io/article/69-sending-domain-setup
 */
try {
    $mailtrap = MailtrapClient::initSendingEmails(
        apiKey: $_ENV['MAILTRAP_API_KEY'], // Your API token from https://mailtrap.io/api-tokens
    );

    $baseEmail = (new MailtrapEmail())
        ->from(new Address('example@YOUR-DOMAIN-HERE.com', 'Mailtrap Test')) // Use your domain installed in Mailtrap
        ->templateUuid('bfa432fd-0000-0000-0000-8493da283a69') // Template UUID
        ->templateVariables([
            'user_name' => 'Jon Bush',
            'next_step_link' => 'https://mailtrap.io/',
            'get_started_link' => 'https://mailtrap.io/',
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
        ]);

    $recipientEmails = [
        (new MailtrapEmail())
            ->to(new Address('recipient1@example.com', 'Recipient 1'))
            // Optional: Override template variables for this recipient
            ->templateVariables([
                'user_name' => 'Custom User 1',
            ]),
        (new MailtrapEmail())
            ->to(new Address('recipient2@example.com', 'Recipient 2')),
    ];

    $response = $mailtrap->batchSend($recipientEmails, $baseEmail);

    var_dump(ResponseHelper::toArray($response)); // Output response body as array
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}
