<?php
# src/Command/SendSandboxMailCommand.php
# php bin/console app:send-sandbox-mail

namespace App\Command;

use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Mime\Address;

#[AsCommand(name: 'app:send-sandbox-mail')]
final class SendSandboxMailCommand
{
    public function __invoke(): int { // Available since Symfony 7.0. For earlier versions, use the execute() method instead.
        $email = (new MailtrapEmail())
            ->from(new Address('hello@example.com', 'Mailtrap Sandbox'))
            ->to(new Address("email@gmail.com"))
            ->subject('You are awesome!')
            ->text('Congrats for sending test email with Mailtrap!')
        ;

        $response = MailtrapClient::initSendingEmails(
            apiKey:    $_ENV['MAILTRAP_API_KEY'], // your API token from here https://mailtrap.io/api-tokens
            isSandbox: true,
            inboxId:   $_ENV['MAILTRAP_INBOX_ID'], // your Inbox ID from here https://mailtrap.io/inboxes
        )->send($email);

        var_dump(ResponseHelper::toArray($response));

        return Command::SUCCESS;
    }
}
