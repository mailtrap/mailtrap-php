Mailtrap bridge for Symfony framework [SDK]
===============

Provides full mailtrap.io integration for Symfony Mailer.

## Installation
If you just want to get started quickly with the SDK library, you should run the following command:
```bash
composer require railsware/mailtrap-php symfony/http-client nyholm/psr7 symfony/mailer
```

## Usage

Add MailtrapTransport into your `config/services.yaml` file
```yaml
...
    # add more service definitions when explicit configuration is needed
    # please note that last definitions always *replace* previous ones

    Mailtrap\Bridge\Transport\MailtrapSdkTransportFactory:
        tags:
            - { name: 'mailer.transport_factory' }
```

### Sending
Add or change MAILER_DSN variable inside your `.env` file. Also, you need to change the `YOUR_API_KEY_HERE` placeholder.
```bash
MAILER_DSN=mailtrap+sdk://YOUR_API_KEY_HERE@default
# or
MAILER_DSN=mailtrap+sdk://YOUR_API_KEY_HERE@send.api.mailtrap.io
```
The template and batch examples below call the API client directly and read the API token from
`MAILTRAP_API_KEY`. Add it to your `.env` file too if you use them:
```bash
MAILTRAP_API_KEY=YOUR_API_KEY_HERE
```

### Bulk Sending
Add or change MAILER_DSN variable inside your `.env` file. Also, you need to change the `YOUR_API_KEY_HERE` placeholder.

More info about bulk sending -> https://help.mailtrap.io/article/113-sending-streams
```bash
MAILER_DSN=mailtrap+sdk://YOUR_API_KEY_HERE@bulk.api.mailtrap.io
```

### Sandbox
Add or change MAILER_DSN variable inside your `.env` file. Also, you need to change the `YOUR_API_KEY_HERE` placeholder and put correct `sandboxId`.

More info sandbox -> https://help.mailtrap.io/article/109-getting-started-with-mailtrap-email-testing
```bash
MAILER_DSN=mailtrap+sdk://YOUR_API_KEY_HERE@sandbox.api.mailtrap.io?sandboxId=1000001
```
The deprecated `inboxId` option still works.

### Send your first email

#### CLI command (the mailer:test command was introduced only in Symfony 6.2)
```bash
php bin/console mailer:test to@example.com
```

#### Controller (base example)

```php
<?php

use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Mailtrap\Helper\ResponseHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;


final class SomeController extends AbstractController
{
    private TransportInterface $transport;

    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    #[Route('/send-email', name: 'send-email', methods: ['GET'])]
    public function sendEmail(): JsonResponse
    {
        $message = (new MailtrapEmail())
            ->from('from@example.com')
            ->to('to@example.com')
            ->cc('cc@example.com')
            ->bcc('bcc@example.com')
            ->replyTo('fabien@example.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Test email')
            ->text('text')
            ->category('category')
            ->customVariables([
                'var1' => 'value1',
                'var2' => 'value2'
            ])
        ;

        $response = $this->transport->send($message);

        return new JsonResponse(['messageId' => $response->getMessageId()]);
    }
    
    /**
     * WARNING! To send using Mailtrap Email Template, you should use the native library and its methods,
     * as mail transport validation does not allow you to send emails without ‘html’ or ‘text’
     */
    #[Route('/send-template-email', name: 'send-template-email', methods: ['GET'])]
    public function sendTemplateEmail(): JsonResponse
    {
        $email = (new MailtrapEmail())
            ->from(new Address('example@YOUR-DOMAIN-HERE.com', 'Mailtrap Test')) // <--- you should use your domain here that you installed in the mailtrap.io admin area (otherwise you will get 401)
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

        return new JsonResponse(ResponseHelper::toArray($response));
    }
    
    /**
     * WARNING! To send Batch Email, you should use the native library and its methods
     */
    #[Route('/send-batch-email', name: 'send-batch-email', methods: ['GET'])]
    public function sendBatchEmail(): JsonResponse
    {
        // Choose either Transactional API or Bulk API
        // For Transactional API
        $mailtrap = MailtrapClient::initSendingEmails(
            apiKey: $_ENV['MAILTRAP_API_KEY'], // Your API token from https://mailtrap.io/api-tokens
        );
    
        // OR for Bulk API (uncomment the line below and comment out the transactional initialization)
        // $mailtrap = MailtrapClient::initSendingEmails(
        //    apiKey: $_ENV['MAILTRAP_API_KEY'], // Your API token from https://mailtrap.io/api-tokens
        //    isBulk: true // Enable bulk sending
        //);
    
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

        return new JsonResponse(ResponseHelper::toArray($response));
    }
}
```

### Category and custom variables

Set them with the `MailtrapEmail` methods shown above, or with Symfony's standard headers
(since version 3.19.0):

```php
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\Header\TagHeader;
use Symfony\Component\Mime\Email;

$email = (new Email())
    ->from('from@example.com')
    ->to('to@example.com')
    ->subject('Test email')
    ->text('text')
;
$email->getHeaders()
    ->add(new TagHeader('category'))               // the Mailtrap category
    ->add(new MetadataHeader('var1', 'value1'))    // a Mailtrap custom variable
;

$this->transport->send($email);
```

An email can have one category: a second `TagHeader`, or a `TagHeader` together with
`category()`, fails the send.

## Examples
See the consolidated [examples Index](../../../examples/README.md) for all scripts. Symfony-focused runnable scripts:
- Transactional send: [`examples/symfony/transactional.php`](../../../examples/symfony/transactional.php)
- Sandbox send: [`examples/symfony/sandbox.php`](../../../examples/symfony/sandbox.php)
- Template send: [`examples/symfony/template.php`](../../../examples/symfony/template.php)
- Bulk stream send: [`examples/symfony/bulk.php`](../../../examples/symfony/bulk.php)

## Resources

* [Symfony mailer documentation](https://symfony.com/doc/current/mailer.html)

### Notes
> If you only send emails through Symfony Mailer, you can also use Symfony's own
> [Mailtrap bridge](https://symfony.com/packages/MailtrapMailer) (`symfony/mailtrap-mailer`).
> It supports the category and custom variables through `TagHeader` and `MetadataHeader` too.
> Choose this SDK when you also need the Mailtrap API client, for example for batch sending,
> templates, contacts or domains.
