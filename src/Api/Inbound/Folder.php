<?php

declare(strict_types=1);

namespace Mailtrap\Api\Inbound;

use Mailtrap\Api\AbstractApi;
use Mailtrap\DTO\Request\Inbound\CreateInboundFolder;
use Mailtrap\DTO\Request\Inbound\UpdateInboundFolder;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Folder
 *
 * Inbound folders.
 */
class Folder extends AbstractApi implements InboundInterface
{
    public function getList(): ResponseInterface
    {
        return $this->handleResponse($this->httpGet($this->getBasePath()));
    }

    public function getById(int $folderId): ResponseInterface
    {
        return $this->handleResponse($this->httpGet($this->getBasePath() . '/' . $folderId));
    }

    public function create(CreateInboundFolder $folder): ResponseInterface
    {
        return $this->handleResponse($this->httpPost($this->getBasePath(), [], $folder->toArray()));
    }

    public function update(int $folderId, UpdateInboundFolder $folder): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPatch($this->getBasePath() . '/' . $folderId, [], $folder->toArray())
        );
    }

    public function delete(int $folderId): ResponseInterface
    {
        return $this->handleResponse($this->httpDelete($this->getBasePath() . '/' . $folderId));
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/inbound/folders', $this->getHost());
    }
}
