Mailtrap bridge for Laravel framework [SDK]
===============

Provides full mailtrap.io integration for Laravel framework.

## Installation
If you just want to get started quickly, you should run one of the following command (depends on which HTTP client you want to use):
```bash
# With symfony http client (recommend)
composer require railsware/mailtrap-php symfony/http-client nyholm/psr7 symfony/mailer

# Or with guzzle http client
composer require railsware/mailtrap-php guzzlehttp/guzzle php-http/guzzle7-adapter symfony/mailer
```

## Usage

Add mailtrap transport into your `config/mail.php` file.
```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    */
    'mailers' => [
    
            // start mailtrap transport
            'mailtrap-sdk' => [
                'transport' => 'mailtrap-sdk'
            ],
            // end mailtrap transport
    
    ]
];
```

Set `mailtrap-sdk` transport as a default Laravel mailer and change default mailtrap config variables inside your `.env` file.


### Sending
You need to set the API key to the `MAILTRAP_API_KEY` variable.
```bash
MAIL_MAILER="mailtrap-sdk"

MAILTRAP_HOST="send.api.mailtrap.io"
MAILTRAP_API_KEY="YOUR_API_KEY_HERE"
```
### Bulk Sending
You need to set the API key to the `MAILTRAP_API_KEY` variable.

More info about bulk sending -> https://help.mailtrap.io/article/113-sending-streams
```bash
MAIL_MAILER="mailtrap-sdk"

MAILTRAP_HOST="bulk.api.mailtrap.io"
MAILTRAP_API_KEY="YOUR_API_KEY_HERE"
```
### Sandbox
You need to set the API key to the `MAILTRAP_API_KEY` variable and set your inboxId to the `MAILTRAP_INBOX_ID`.

More info sandbox -> https://help.mailtrap.io/article/109-getting-started-with-mailtrap-email-testing
```bash
MAIL_MAILER="mailtrap-sdk"

MAILTRAP_HOST="sandbox.api.mailtrap.io"
MAILTRAP_API_KEY="YOUR_API_KEY_HERE"
MAILTRAP_INBOX_ID=1000001
```

Before starting, run the clear configuration cache command to set up new variables.
```bash
php artisan config:clear
```

### Send you first email
Firstly you need to generate `Mailable` class. More info [here](https://laravel.com/docs/10.x/mail#generating-mailables)
```bash
php artisan make:mail WelcomeMail
```
After that, you can configure your Email as you wish. Below will be an example.
```php
# app/Mail/WelcomeMail.php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Mailtrap\EmailHeader\CategoryHeader;
use Mailtrap\EmailHeader\CustomVariableHeader;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\UnstructuredHeader;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    private string $name;

    /**
     * Create a new message instance.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('jeffrey@example.com', 'Jeffrey Way'),
            replyTo: [
                      new Address('taylor@example.com', 'Taylor Otwell'),
                  ],
            subject: 'Welcome Mail',
            using: [
                      function (Email $email) {
                          // Headers
                          $email->getHeaders()
                              ->addTextHeader('X-Message-Source', 'example.com')
                              ->add(new UnstructuredHeader('X-Mailer', 'Mailtrap PHP Client'))
                          ;

                          // Custom Variables
                          $email->getHeaders()
                              ->add(new CustomVariableHeader('user_id', '45982'))
                              ->add(new CustomVariableHeader('batch_id', 'PSJ-12'))
                          ;

                          // Category (should be only one)
                          $email->getHeaders()
                              ->add(new CategoryHeader('Integration Test'))
                          ;
                      },
                  ]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.welcome-email',
            with: ['name' => $this->name],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath('https://mailtrap.io/wp-content/uploads/2021/04/mailtrap-new-logo.svg')
                ->as('logo.svg')
                ->withMime('image/svg+xml'),
        ];
    }

    /**
     * Get the message headers.
     */
    public function headers(): Headers
    {
        return new Headers(
            'custom-message-id@example.com',
            ['previous-message@example.com'],
            [
                'X-Custom-Header' => 'Custom Value',
            ],
        );
    }
}
```
Create email template `resources/views/mail/welcome-email.blade.php`
```php
# resources/views/mail/welcome-email.blade.php

Hey, {{$name}} and welcome here 😉

<br>
Funny Coder
```

Add CLI router
```php
# app/routes/console.php
<?php

use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
*/

Artisan::command('send-welcome-mail', function () {
    Mail::to('testreceiver@gmail.com')->send(new WelcomeMail("Jon"));
    
    // Also, you can use specific mailer if your default mailer is not "mailtrap-sdk" but you want to use it for welcome mails
    // Mail::mailer('mailtrap-sdk')->to('testreceiver@gmail.com')->send(new WelcomeMail("Jon"));
})->purpose('Send welcome mail');
```

After that just call this CLI command, and it will send your email
```bash
php artisan send-welcome-mail
```

### Send Template Email
To send using Mailtrap Email Template, you should use the native library and its methods,
as mail transport validation does not allow you to send emails without ‘html’ or ‘text’.

Add CLI command
```php
# app/routes/console.php
<?php

use Illuminate\Support\Facades\Artisan;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

Artisan::command('send-template-mail', function () {
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

    MailtrapClient::initSendingEmails(
        apiKey: config('services.mailtrap-sdk.apiKey') // your API token from here https://mailtrap.io/api-tokens
    )->send($email);
})->purpose('Send Template Mail');
```

After that, just call this CLI command, and it will send your template email
```bash
php artisan send-template-mail
```

### Batch Sending (Transactional OR Bulk)

Add CLI command
```php
# app/routes/console.php
<?php

use Illuminate\Support\Facades\Artisan;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

Artisan::command('batch-send-mail', function () {
    // Choose either Transactional API or Bulk API
    // For Transactional API
    $mailtrap = MailtrapClient::initSendingEmails(
        apiKey: config('services.mailtrap-sdk.apiKey'), // Your API token from https://mailtrap.io/api-tokens
    );

    // OR for Bulk API (uncomment the line below and comment out the transactional initialization)
    // $mailtrap = MailtrapClient::initSendingEmails(
    //    apiKey: config('services.mailtrap-sdk.apiKey'), // Your API token from https://mailtrap.io/api-tokens
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

    $mailtrap->batchSend($recipientEmails, $baseEmail);
})->purpose('Send Batch Mail');
```

After that, just call this CLI command, and it will send your batch emails
```bash
php artisan batch-send-mail
```

## Examples
See the consolidated [examples Index](../../../examples/README.md) for all scripts. Laravel-focused runnable scripts:
- Transactional send: [`examples/laravel/transactional.php`](../../../examples/laravel/transactional.php)
- Sandbox send: [`examples/laravel/sandbox.php`](../../../examples/laravel/sandbox.php)
- Template send: [`examples/laravel/template.php`](../../../examples/laravel/template.php)
- Bulk stream send: [`examples/laravel/bulk.php`](../../../examples/laravel/bulk.php)


## Compatibility
The Mailtrap library is fully compatible with **Laravel 9.x and above**.
> Laravel did one of the largest changes in Laravel 9.x is the transition from SwiftMailer, which is no longer maintained as of December 2021, to Symfony Mailer.
>
> You can find more information from these two URLs:
>
> https://laravel.com/docs/9.x/releases#symfony-mailer and 
> https://laravel.com/docs/9.x/upgrade#symfony-mailer  


But you can still use this library as a standalone. More example how to use, you can find [here](../../../examples)

### WARNING
If you encounter the `IncompleteDsnException` error, it is likely that you are using an outdated Laravel version 
and the configuration parameters were not set automatically by Laravel service provider.
Please add them manually to the `config/services.php` file and after that run the `php artisan config:clear` command.

```php
'mailtrap-sdk' => [
    'host' => env('MAILTRAP_HOST', 'send.api.mailtrap.io'),
    'apiKey' => env('MAILTRAP_API_KEY'),
    'inboxId' => env('MAILTRAP_INBOX_ID'),
],
```

## Resources

* [Laravel mail documentation](https://laravel.com/docs/master/mail)
