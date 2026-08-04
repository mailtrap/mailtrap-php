<?php

declare(strict_types=1);

namespace Mailtrap\Api\Inbound;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Thread
 *
 * Conversation threads in an inbound inbox.
 */
class Thread extends AbstractApi implements InboundInterface
{
    public function __construct(ConfigInterface $config, private int $inboxId)
    {
        parent::__construct($config);
    }

    /**
     * List threads. Pass $lastId from a previous response to fetch the next page.
     */
    public function getList(?string $lastId = null): ResponseInterface
    {
        $parameters = $lastId !== null ? ['last_id' => $lastId] : [];

        return $this->handleResponse($this->httpGet($this->getBasePath(), $parameters));
    }

    public function getById(string $threadId): ResponseInterface
    {
        return $this->handleResponse($this->httpGet($this->getBasePath() . '/' . $threadId));
    }

    public function delete(string $threadId): ResponseInterface
    {
        return $this->handleResponse($this->httpDelete($this->getBasePath() . '/' . $threadId));
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/inbound/inboxes/%s/threads', $this->getHost(), $this->inboxId);
    }
}
