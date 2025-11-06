<?php

use Mailtrap\Config;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\HttpClient\HttpClientBuilder;
use Mailtrap\MailtrapSendingClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\Mime\Address;

require __DIR__ . '/../vendor/autoload.php';


$apiToken = $_ENV['MAILTRAP_API_KEY'];
$config = new Config($apiToken);

// Set a custom PSR-18 HTTP client (Symfony HTTP Client in this case)
$config->setHttpClient(
    new Psr18Client()
);

// Set a custom HTTP client builder
$config->setHttpClientBuilder(
    new HttpClientBuilder($apiToken)
);

// Enable/Disable throwing exceptions on HTTP errors (by default it's enabled)
$config->setResponseThrowOnError(true);


// Now you can use $config to interact with Mailtrap Sending API
$sendingClient = (new MailtrapSendingClient($config))->emails();

try {
    $email = (new MailtrapEmail())
        ->from(new Address('example@YOUR-DOMAIN-HERE.com', 'Mailtrap Test')) // <--- you should use your domain here that you installed in the mailtrap.io admin area (otherwise you will get 401)
        ->to(new Address('email@example.com', 'Jon'))
        ->subject('Hello from Mailtrap!')
        ->text('Welcome to Mailtrap Sending!')
    ;

    $response = $sendingClient->send($email);

    var_dump(ResponseHelper::toArray($response)); // body (array)
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
