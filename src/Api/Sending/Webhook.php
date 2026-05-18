<?php

declare(strict_types=1);

namespace Mailtrap\Api\Sending;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Mailtrap\DTO\Request\Webhook\CreateWebhook;
use Mailtrap\DTO\Request\Webhook\UpdateWebhook;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Webhook
 */
class Webhook extends AbstractApi implements SendingInterface
{
    public function __construct(ConfigInterface $config, private int $accountId)
    {
        parent::__construct($config);
    }

    /**
     * Get a list of webhooks for the account.
     *
     * @return ResponseInterface
     */
    public function getWebhooks(): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath())
        );
    }

    /**
     * Get a webhook by ID.
     *
     * @param int $webhookId
     * @return ResponseInterface
     */
    public function getWebhook(int $webhookId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath() . '/' . $webhookId)
        );
    }

    /**
     * Create a new webhook. The response includes a `signing_secret` used to verify webhook signatures.
     *
     * @param CreateWebhook $webhook
     * @return ResponseInterface
     */
    public function createWebhook(CreateWebhook $webhook): ResponseInterface
    {
        return $this->handleResponse($this->httpPost(
            path: $this->getBasePath(),
            body: ['webhook' => $webhook->toArray()]
        ));
    }

    /**
     * Update an existing webhook by ID. Only mutable fields can be updated.
     *
     * @param int           $webhookId
     * @param UpdateWebhook $webhook
     * @return ResponseInterface
     */
    public function updateWebhook(int $webhookId, UpdateWebhook $webhook): ResponseInterface
    {
        return $this->handleResponse($this->httpPatch(
            path: $this->getBasePath() . '/' . $webhookId,
            body: ['webhook' => $webhook->toArray()]
        ));
    }

    /**
     * Delete a webhook by ID.
     *
     * @param int $webhookId
     * @return ResponseInterface
     */
    public function deleteWebhook(int $webhookId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpDelete($this->getBasePath() . '/' . $webhookId)
        );
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/accounts/%s/webhooks', $this->getHost(), $this->accountId);
    }
}
