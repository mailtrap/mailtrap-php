<?php

use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\UnstructuredHeader;

require __DIR__ . '/../vendor/autoload.php';

/**
 * Email Bulk Sending API
 *
 * POST https://bulk.api.mailtrap.io/api/send
 */
try {
    $bulkMailtrap = MailtrapClient::initSendingEmails(
        apiKey: $_ENV['MAILTRAP_API_KEY'], #your API token from here https://mailtrap.io/api-tokens
        isBulk: true # Bulk sending (@see https://help.mailtrap.io/article/113-sending-streams)
    );

    $email = (new MailtrapEmail())
        ->from(new Address('example@YOUR-DOMAIN-HERE.com', 'Mailtrap Test')) // <--- you should use your domain here that you installed in the mailtrap.io admin area (otherwise you will get 401)
        ->replyTo(new Address('reply@YOUR-DOMAIN-HERE.com'))
        ->to(new Address('email@example.com', 'Jon'))
        ->priority(Email::PRIORITY_HIGH)
        ->cc('mailtrapqa@example.com')
        ->addCc('staging@example.com')
        ->bcc('mailtrapdev@example.com')
        ->subject('Best practices of building HTML emails')
        ->text('Hey! Learn the best practices of building HTML emails and play with ready-to-go templates. Mailtrap’s Guide on How to Build HTML Email is live on our blog')
        ->html(
            '<html>
            <body>
            <p><br>Hey</br>
            Learn the best practices of building HTML emails and play with ready-to-go templates.</p>
            <p><a href="https://mailtrap.io/blog/build-html-email/">Mailtrap’s Guide on How to Build HTML Email</a> is live on our blog</p>
            <img src="cid:logo">
            </body>
        </html>'
        )
        ->embed(fopen('https://mailtrap.io/wp-content/uploads/2021/04/mailtrap-new-logo.svg', 'r'), 'logo', 'image/svg+xml')
        ->attachFromPath('README.md')
        ->customVariables([
          'user_id' => '45982',
          'batch_id' => 'PSJ-12'
        ])
        ->category('Integration Test')
    ;

    // Custom email headers (optional)
    $email->getHeaders()
        ->addTextHeader('X-Message-Source', 'test.com')
        ->add(new UnstructuredHeader('X-Mailer', 'Mailtrap PHP Client'))
    ;

    $response = $bulkMailtrap->send($email);

    var_dump(ResponseHelper::toArray($response)); // body (array)
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
