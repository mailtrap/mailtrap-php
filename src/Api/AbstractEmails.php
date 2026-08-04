<?php

declare(strict_types=1);

namespace Mailtrap\Api;

use Mailtrap\Exception\LogicException;
use Symfony\Component\Mime\Email;

/**
 * Class AbstractEmails
 */
abstract class AbstractEmails extends AbstractApi implements EmailsSendApiInterface
{
    use EmailPayloadTrait;

    protected function getBatchBasePayload(Email $email): array
    {
        $payload = $this->getPayload($email);
        if (!empty($payload['to']) || !empty($payload['cc']) || !empty($payload['bcc'])) {
            throw new LogicException(
                "Batch base email does not support 'to', 'cc', or 'bcc' fields. Please use individual batch email requests to specify recipients."
            );
        }

        if (!empty($this->getFirstReplyTo($email->getHeaders()))) {
            $payload['reply_to'] = $this->getStringifierAddress(
                $this->getFirstReplyTo($email->getHeaders())
            );
        }

        return $payload;
    }

    protected function getBatchBody(array $recipientEmails, ?Email $baseEmail = null): array
    {
        $body = [];
        if ($baseEmail !== null) {
            $body['base'] = $this->getBatchBasePayload($baseEmail);
        }

        $body['requests'] = array_map(
            fn(Email $email) => $this->getPayload($email),
            $recipientEmails
        );

        return $body;
    }
}
