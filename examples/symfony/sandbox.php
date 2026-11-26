<?php
# src/Command/SendSandboxMailCommand.php
# .env: MAILER_DSN=mailtrap+sdk://YOUR_API_KEY_HERE@sandbox.api.mailtrap.io?sandboxId=1000001
# php bin/console app:send-sandbox-mail

namespace App\Command;

use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;

#[AsCommand(name: 'app:send-sandbox-mail')]
final class SendSandboxMailCommand
{
    // The transport built from MAILER_DSN. The sandbox.api.mailtrap.io host and sandboxId select the sandbox.
    public function __construct(private TransportInterface $transport)
    {
    }

    public function __invoke(): int { // Invokable commands are available since Symfony 7.3. For earlier versions, extend Command and use the execute() method instead.
        $email = (new MailtrapEmail())
            ->from(new Address('hello@example.com', 'Mailtrap Sandbox'))
            ->to(new Address("email@gmail.com"))
            ->subject('You are awesome!')
            ->text('Congrats for sending test email with Mailtrap!')
        ;

        $sentMessage = $this->transport->send($email);

        var_dump($sentMessage?->getMessageId());

        return Command::SUCCESS;
    }
}
