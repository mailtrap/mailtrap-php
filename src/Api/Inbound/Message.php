<?php

declare(strict_types=1);

namespace Mailtrap\Api\Inbound;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\EmailPayloadTrait;
use Mailtrap\ConfigInterface;
use Mailtrap\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Mime\Email;

/**
 * Class Message
 *
 * Received messages in an inbound inbox.
 * reply/replyAll/forward accept a Symfony Mime Email, exactly like the send API.
 */
class Message extends AbstractApi implements InboundInterface
{
    use EmailPayloadTrait;

    public function __construct(ConfigInterface $config, private int $inboxId)
    {
        parent::__construct($config);
    }

    /**
     * List received messages. Pass $lastId from a previous response to fetch
     * the next page.
     */
    public function getList(?string $lastId = null): ResponseInterface
    {
        $parameters = $lastId !== null ? ['last_id' => $lastId] : [];

        return $this->handleResponse($this->httpGet($this->getBasePath(), $parameters));
    }

    public function getById(string $messageId): ResponseInterface
    {
        return $this->handleResponse($this->httpGet($this->getBasePath() . '/' . $messageId));
    }

    public function delete(string $messageId): ResponseInterface
    {
        return $this->handleResponse($this->httpDelete($this->getBasePath() . '/' . $messageId));
    }

    /**
     * Reply to a message (to the original sender). Sends a real email.
     */
    public function reply(string $messageId, Email $email): ResponseInterface
    {
        return $this->handleResponse($this->httpPost(
            $this->getBasePath() . '/' . $messageId . '/reply',
            [],
            $this->getPayload($email)
        ));
    }

    /**
     * Reply to a message and copy the original's other recipients. Sends a real email.
     */
    public function replyAll(string $messageId, Email $email): ResponseInterface
    {
        return $this->handleResponse($this->httpPost(
            $this->getBasePath() . '/' . $messageId . '/reply_all',
            [],
            $this->getPayload($email)
        ));
    }

    /**
     * Forward a message to new recipients (at least one "to" is required). Sends a real email.
     */
    public function forward(string $messageId, Email $email): ResponseInterface
    {
        if (empty($email->getTo())) {
            throw new InvalidArgumentException('Forwarding a message requires at least one "to" recipient.');
        }

        return $this->handleResponse($this->httpPost(
            $this->getBasePath() . '/' . $messageId . '/forward',
            [],
            $this->getPayload($email)
        ));
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/inbound/inboxes/%s/messages', $this->getHost(), $this->inboxId);
    }
}
