# Mailtrap PHP client - Official

![GitHub Actions](https://github.com/railsware/mailtrap-php/actions/workflows/ci-phpunit.yml/badge.svg)
![GitHub Actions](https://github.com/railsware/mailtrap-php/actions/workflows/ci-psalm.yaml/badge.svg)

[![PHP version support](https://img.shields.io/packagist/dependency-v/railsware/mailtrap-php/php?style=flat)](https://packagist.org/packages/railsware/mailtrap-php)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/railsware/mailtrap-php.svg?style=flat)](https://packagist.org/packages/railsware/mailtrap-php)
[![Total Downloads](https://img.shields.io/packagist/dt/railsware/mailtrap-php.svg?style=flat)](https://packagist.org/packages/railsware/mailtrap-php)

## Prerequisites

To get the most of this official Mailtrap.io PHP SDK:
- [Create a Mailtrap account](https://mailtrap.io/signup)
- [Verify your domain](https://mailtrap.io/sending/domains)

## Installation
You can install the package via [composer](http://getcomposer.org/)

The Mailtrap API Client is not hard coupled to Guzzle, React, Zend, Symfony HTTP or any other library that sends
HTTP messages. Instead, it uses the [PSR-18](https://www.php-fig.org/psr/psr-18/) client abstraction.

This will give you the flexibility to choose what [HTTP client](https://docs.php-http.org/en/latest/clients.html) you want to use.

If you just want to get started quickly you should run one of the following command (depends on which HTTP client you want to use):
```bash
# With symfony http client (recommend)
composer require railsware/mailtrap-php symfony/http-client nyholm/psr7

# Or with guzzle http client
composer require railsware/mailtrap-php guzzlehttp/guzzle php-http/guzzle7-adapter
```

## Framework integration
If you use a framework, install a bridge package for seamless configuration:

* [Symfony](src/Bridge/Symfony)
* [Laravel](src/Bridge/Laravel)

These provide service registration and allow you to inject the client where needed with minimal manual bootstrapping.

## Usage
You should use Composer autoloader in your application to automatically load your dependencies. 

### Minimal usage (Transactional sending)
The quickest way to send a single transactional email with only the required parameters:

```php
<?php

use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

require __DIR__ . '/vendor/autoload.php';

$mailtrap = MailtrapClient::initSendingEmails(
    apiKey: getenv('MAILTRAP_API_KEY') // your API key here https://mailtrap.io/api-tokens
);

$email = (new MailtrapEmail())
    ->from(new Address('sender@example.com'))
    ->to(new Address('recipient@example.com'))
    ->subject('Hello from Mailtrap PHP')
    ->text('Plain text body');

$response = $mailtrap->send($email);

// Access response body as array (helper optional)
var_dump(ResponseHelper::toArray($response));
```

### Sandbox vs Production (easy switching)
Mailtrap lets you test safely in the Email Sandbox and then switch to Production (Sending) with one flag.

Example `.env` variables (or export in shell):
```
MAILTRAP_API_KEY=your_api_token # https://mailtrap.io/api-tokens
MAILTRAP_USE_SANDBOX=true       # true/false toggle
MAILTRAP_INBOX_ID=123456        # Only needed for sandbox
```

Bootstrap logic:
```php
<?php

use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

require __DIR__ . '/vendor/autoload.php';

$apiKey    = getenv('MAILTRAP_API_KEY');
$isSandbox = filter_var(getenv('MAILTRAP_USE_SANDBOX'), FILTER_VALIDATE_BOOL);
$inboxId   = $isSandbox ? getenv('MAILTRAP_INBOX_ID') : null; // required only for sandbox

$client = MailtrapClient::initSendingEmails(
    apiKey: $apiKey,
    isSandbox: $isSandbox,
    inboxId: $inboxId // null is ignored for production
);

$email = (new MailtrapEmail())
    ->from(new Address($isSandbox ? 'sandbox@example.com' : 'no-reply@your-domain.com'))
    ->to(new Address('recipient@example.com'))
    ->subject($isSandbox ? '[SANDBOX] Demo email' : 'Welcome onboard')
    ->text('This is a minimal body for demonstration purposes.');

$response = $client->send($email);

// Access response body as array (helper optional)
var_dump(ResponseHelper::toArray($response));
```

Bulk stream example (optional) differs only by setting `isBulk: true`:
```php
$bulkClient = MailtrapClient::initSendingEmails(apiKey: $apiKey, isBulk: true);
```

Recommendations:
- Toggle sandbox with `MAILTRAP_USE_SANDBOX`.
- Use separate API tokens for Production and Sandbox.
- Keep initialisation in a single factory object/service so that switching is centralised.

### Full-featured usage example

```php
<?php

use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\UnstructuredHeader;

require __DIR__ . '/vendor/autoload.php';

// Init Mailtrap client depending on your needs
$mailtrap = MailtrapClient::initSendingEmails(
    apiKey: getenv('MAILTRAP_API_KEY'), # your API token
    isBulk: false,                      # set to true for bulk email sending (false by default)
    isSandbox: false,                   # set to true for sandbox mode       (false by default)
    inboxId: null                       # optional, only for sandbox mode    (false by default)
);

$email = (new MailtrapEmail())
    ->from(new Address('example@your-domain-here.com', 'Mailtrap Test'))
    ->replyTo(new Address('reply@your-domain-here.com'))
    ->to(new Address('email@example.com', 'Jon'))
    ->priority(Email::PRIORITY_HIGH)
    ->cc('mailtrapqa@example.com')
    ->addCc('staging@example.com')
    ->bcc('mailtrapdev@example.com')
    ->subject('Best practices of building HTML emails')
    ->text('Hey! Learn the best practices of building HTML emails and play with ready-to-go templates. Mailtrap\'s Guide on How to Build HTML Email is live on our blog')
    ->html(
        '<html>
        <body>
        <p><br>Hey</br>
        Learn the best practices of building HTML emails and play with ready-to-go templates.</p>
        <p><a href="https://mailtrap.io/blog/build-html-email/">Mailtrap\'s Guide on How to Build HTML Email</a> is live on our blog</p>
        <img src="cid:logo">
        </body>
    </html>'
    )
    ->embed(fopen('https://mailtrap.io/wp-content/uploads/2021/04/mailtrap-new-logo.svg', 'r'), 'logo', 'image/svg+xml')
    ->category('Integration Test')
    ->customVariables([
        'user_id' => '45982',
        'batch_id' => 'PSJ-12'
    ])
;

// Custom email headers (optional)
$email->getHeaders()
    ->addTextHeader('X-Message-Source', 'domain.com')
    ->add(new UnstructuredHeader('X-Mailer', 'Mailtrap PHP Client')) // the same as addTextHeader
;

try {
    $response = $mailtrap->send($email);

    var_dump(ResponseHelper::toArray($response)); // body (array)
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


// OR Template email sending

$email = (new MailtrapEmail())
    ->from(new Address('example@your-domain-here.com', 'Mailtrap Test'))
    ->replyTo(new Address('reply@your-domain-here.com'))
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

try {
    $response = $mailtrap->send($email);

    var_dump(ResponseHelper::toArray($response)); // body (array)
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
```

## Supported functionality & Examples

Email API:
- Send an email (Transactional stream) – [`sending/minimal.php`](examples/sending/minimal.php)
- Send an email (Bulk stream) – [`bulk/bulk.php`](examples/bulk/bulk.php)
- Send an email with a Template (Transactional) – [`sending/template.php`](examples/sending/template.php)
- Send an email with a Template (Bulk) – [`bulk/bulk_template.php`](examples/bulk/bulk_template.php)
- Batch send (Transactional) – [`batch/transactional.php`](examples/batch/transactional.php)
- Batch send (Bulk) – [`batch/bulk.php`](examples/batch/bulk.php)
- Batch send with Template (Transactional) – [`batch/transactional_template.php`](examples/batch/transactional_template.php)
- Batch send with Template (Bulk) – [`batch/bulk_template.php`](examples/batch/bulk_template.php)
- Sending domain management CRUD – [`sending-domains/all.php`](examples/sending-domains/all.php)

Email Sandbox (Testing):
- Send an email (Sandbox) – [`testing/send-mail.php`](examples/testing/send-mail.php)
- Send an email with a Template (Sandbox) – [`testing/template.php`](examples/testing/template.php)
- Batch send (Sandbox) – [`batch/sandbox.php`](examples/batch/sandbox.php)
- Batch send with Template (Sandbox) – [`batch/sandbox_template.php`](examples/batch/sandbox_template.php)
- Message management CRUD – [`testing/messages.php`](examples/testing/messages.php)
- Inbox management CRUD – [`testing/inboxes.php`](examples/testing/inboxes.php)
- Project management CRUD – [`testing/projects.php`](examples/testing/projects.php)
- Attachments operations – [`testing/attachments.php`](examples/testing/attachments.php)

Contact management:
- Contacts CRUD & listing – [`contacts/all.php`](examples/contacts/all.php)
- Contact lists CRUD – [`contact-lists/all.php`](examples/contact-lists/all.php)
- Custom fields CRUD – [`contact-fields/all.php`](examples/contact-fields/all.php)
- Import/Export – (no example yet) ← add in future
- Events – (no example yet) ← add in future

General API:
- Templates CRUD – [`templates/all.php`](examples/templates/all.php)
- Suppressions (find & delete) – [`sending/suppressions.php`](examples/sending/suppressions.php)
- Billing info – [`general/billing.php`](examples/general/billing.php)
- Accounts info – [`general/accounts.php`](examples/general/accounts.php)
- Permissions listing – [`general/permissions.php`](examples/general/permissions.php)
- Users listing – [`general/users.php`](examples/general/users.php)

Framework-specific (quick starts):
- Laravel transactional send – [`laravel/transactional.php`](examples/laravel/transactional.php)
- Laravel sandbox send – [`laravel/sandbox.php`](examples/laravel/sandbox.php)
- Laravel template send – [`laravel/template.php`](examples/laravel/template.php)
- Laravel bulk send – [`laravel/bulk.php`](examples/laravel/bulk.php)
- Symfony transactional send – [`symfony/transactional.php`](examples/symfony/transactional.php)
- Symfony sandbox send – [`symfony/sandbox.php`](examples/symfony/sandbox.php)
- Symfony template send – [`symfony/template.php`](examples/symfony/template.php)
- Symfony bulk send – [`symfony/bulk.php`](examples/symfony/bulk.php)

See the full indexed list at [`examples/README.md`](examples/README.md).

## Contributing

Bug reports and pull requests are welcome on [GitHub](https://github.com/railsware/mailtrap-php). This project is intended to be a safe, welcoming space for collaboration, and contributors are expected to adhere to the [code of conduct](CODE_OF_CONDUCT.md).

## License

The package is available as open source under the terms of the [MIT License](https://opensource.org/licenses/MIT).

## Code of Conduct

Everyone interacting in the Mailtrap project's codebases, issue trackers, chat rooms and mailing lists is expected to follow the [code of conduct](CODE_OF_CONDUCT.md).
