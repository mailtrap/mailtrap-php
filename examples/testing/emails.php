<?php

use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\UnstructuredHeader;

require __DIR__ . '/../vendor/autoload.php';


/**********************************************************************************************************************
 ******************************************* EMAIL TESTING ************************************************************
 **********************************************************************************************************************
 */

/**
 * Email Testing API
 *
 * POST https://sandbox.api.mailtrap.io/api/send/{inbox_id}
 */
try {
    $mailtrap = MailtrapClient::initSendingEmails(
        apiKey: getenv('MAILTRAP_API_KEY'), #your API token from here https://mailtrap.io/api-tokens
        isSandbox: true, # Sandbox sending (@see https://help.mailtrap.io/article/109-getting-started-with-mailtrap-email-testing)
        inboxId: getenv('MAILTRAP_INBOX_ID') # required param for sandbox sending
    );

    $email = (new MailtrapEmail())
        ->from(new Address('mailtrap@example.com', 'Mailtrap Test')) // <--- you should use the domain which is linked to template UUID (otherwise you will get 401)
        ->replyTo(new Address('reply@example.com'))
        ->to(new Address('email@example.com', 'Jon'))
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

    $response = $mailtrap->send($email);

    // print all possible information from the response
    var_dump($response->getHeaders()); //headers (array)
    var_dump($response->getStatusCode()); //status code (int)
    var_dump(ResponseHelper::toArray($response)); // body (array)
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Test Email WITH TEMPLATE
 *
 * WARNING! If template is provided then subject, text, html, category  and other params are forbidden.
 *
 * UUID of email template. Subject, text and html will be generated from template using optional template_variables.
 * Optional template variables that will be used to generate actual subject, text and html from email template
 */
try {
    $mailtrap = MailtrapClient::initSendingEmails(
        apiKey: getenv('MAILTRAP_API_KEY'), #your API token from here https://mailtrap.io/api-tokens
        isSandbox: true, # Sandbox sending (@see https://help.mailtrap.io/article/109-getting-started-with-mailtrap-email-testing)
        inboxId: getenv('MAILTRAP_INBOX_ID') # required param for sandbox sending
    );

    $email = (new MailtrapEmail())
        ->from(new Address('example@YOUR-DOMAIN-HERE.com', 'Mailtrap Test')) // <--- you should use the domain which is linked to template UUID (otherwise you will get 401)
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

    $response = $mailtrap->send($email);

    // print all possible information from the response
    var_dump($response->getHeaders()); //headers (array)
    var_dump($response->getStatusCode()); //status code (int)
    var_dump(ResponseHelper::toArray($response)); // body (array)
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
