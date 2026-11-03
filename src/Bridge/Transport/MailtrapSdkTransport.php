<?php

declare(strict_types=1);

namespace Mailtrap\Bridge\Transport;

use Mailtrap\Api\EmailsSendApiInterface;
use Mailtrap\Api\Sandbox\Emails as SandboxEmails;
use Mailtrap\Config;
use Mailtrap\Helper\ResponseHelper;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\RuntimeException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;

/**
 * Class MailtrapSdkTransport
 */
class MailtrapSdkTransport extends AbstractTransport
{
    public function __construct(
        private EmailsSendApiInterface $emailsSendApiLayer,
        private Config $config,
        ?EventDispatcherInterface $dispatcher = null,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($dispatcher, $logger);
    }

    public function __toString(): string
    {
        return sprintf('mailtrap+sdk://%s', $this->getEndpoint());
    }

    protected function doSend(SentMessage $message): void
    {
        try {
            $email = MessageConverter::toEmail($message->getOriginalMessage());
            $envelope = $message->getEnvelope();

            // overrides from the envelope
            $email->from($envelope->getSender());
            $envelopeRecipients = $this->getEnvelopeRecipients($email, $envelope);
            if (!empty($envelopeRecipients)) {
                foreach ($envelopeRecipients as $envelopeRecipient) {
                    $email->addTo($envelopeRecipient);
                }
            }

            $response = $this->emailsSendApiLayer->send($email);

            $body = ResponseHelper::toArray($response);
            $message->setMessageId(implode(',', $body['message_ids']));
        } catch (\Exception $e) {
            throw new RuntimeException(
                sprintf('Unable to send a message with the "%s" transport: ', __CLASS__) . $e->getMessage(), 0, $e
            );
        }
    }

    private function getEndpoint(): string
    {
        $sandboxId = null;
        if ($this->emailsSendApiLayer instanceof SandboxEmails) {
            $sandboxId = $this->emailsSendApiLayer->getSandboxId();
        }

        return $this->config->getHost() . (null === $sandboxId ? '' : '?sandboxId=' . $sandboxId);
    }

    private function getEnvelopeRecipients(Email $email, Envelope $envelope): array
    {
        return array_filter(
            $envelope->getRecipients(),
            static fn (Address $address) => false === in_array($address, array_merge($email->getTo(), $email->getCc(), $email->getBcc()), true)
        );
    }
}
