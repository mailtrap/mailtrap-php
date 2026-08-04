<?php

declare(strict_types=1);

namespace Mailtrap\Api\Inbound;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Mailtrap\DTO\Request\Inbound\CreateInboundInbox;
use Mailtrap\DTO\Request\Inbound\UpdateInboundInbox;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Inbox
 *
 * Inboxes within an inbound folder.
 */
class Inbox extends AbstractApi implements InboundInterface
{
    public function __construct(ConfigInterface $config, private int $folderId)
    {
        parent::__construct($config);
    }

    public function getList(): ResponseInterface
    {
        return $this->handleResponse($this->httpGet($this->getBasePath()));
    }

    public function getById(int $inboxId): ResponseInterface
    {
        return $this->handleResponse($this->httpGet($this->getBasePath() . '/' . $inboxId));
    }

    public function create(CreateInboundInbox $inbox): ResponseInterface
    {
        return $this->handleResponse($this->httpPost($this->getBasePath(), [], $inbox->toArray()));
    }

    public function update(int $inboxId, UpdateInboundInbox $inbox): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPatch($this->getBasePath() . '/' . $inboxId, [], $inbox->toArray())
        );
    }

    public function delete(int $inboxId): ResponseInterface
    {
        return $this->handleResponse($this->httpDelete($this->getBasePath() . '/' . $inboxId));
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/inbound/folders/%s/inboxes', $this->getHost(), $this->folderId);
    }
}
