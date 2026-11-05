<?php

declare(strict_types=1);

namespace Mailtrap\Api\Sandbox;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Mailtrap\DTO\Request\Sandbox as SandboxRequest;
use Mailtrap\Exception\RuntimeException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Sandbox
 */
class Sandbox extends AbstractApi implements SandboxInterface
{
    public function __construct(ConfigInterface $config, private int $accountId)
    {
        parent::__construct($config);
    }

    /**
     * Get a list of sandboxes.
     *
     * @return ResponseInterface
     */
    public function getList(): ResponseInterface
    {
        return $this->handleResponse($this->httpGet(
            sprintf('%s/api/accounts/%s/inboxes', $this->getHost(), $this->getAccountId())
        ));
    }

    /**
     * Get sandbox attributes by sandbox id. See the list of attributes in the example
     *
     * @param int $sandboxId
     *
     * @return ResponseInterface
     */
    public function getSandboxAttributes(int $sandboxId): ResponseInterface
    {
        return $this->handleResponse($this->httpGet(
            sprintf('%s/api/accounts/%s/inboxes/%s', $this->getHost(), $this->getAccountId(), $sandboxId)
        ));
    }

    /**
     * Create a sandbox in a project.
     *
     * @param int    $projectId
     * @param string $sandboxName
     *
     * @return ResponseInterface
     */
    public function create(int $projectId, string $sandboxName): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost(
                sprintf('%s/api/accounts/%s/projects/%s/inboxes', $this->getHost(), $this->getAccountId(), $projectId),
                [],
                ['inbox' => ['name' => $sandboxName]]
            )
        );
    }

    /**
     * Delete a sandbox with all its emails.
     *
     * @param int $sandboxId
     *
     * @return ResponseInterface
     */
    public function delete(int $sandboxId): ResponseInterface
    {
        return $this->handleResponse($this->httpDelete(
            sprintf('%s/api/accounts/%s/inboxes/%s', $this->getHost(), $this->getAccountId(), $sandboxId)
        ));
    }

    /**
     * Update sandbox name and/or sandbox email username.
     *
     * @param int            $sandboxId
     * @param SandboxRequest $sandbox
     *
     * @return ResponseInterface
     */
    public function update(int $sandboxId, SandboxRequest $sandbox): ResponseInterface
    {
        return $this->handleResponse($this->httpPatch(
            sprintf('%s/api/accounts/%s/inboxes/%s', $this->getHost(), $this->getAccountId(), $sandboxId),
            [],
            ['inbox' => $this->getUpdatePayload($sandbox)]
        ));
    }

    /**
     * Delete all messages (emails) from sandbox.
     *
     * @param int $sandboxId
     *
     * @return ResponseInterface
     */
    public function clean(int $sandboxId): ResponseInterface
    {
        return $this->handleResponse($this->httpPatch(
            sprintf('%s/api/accounts/%s/inboxes/%s/clean', $this->getHost(), $this->getAccountId(), $sandboxId)
        ));
    }

    /**
     * Mark all messages in the sandbox as read.
     *
     * @param int $sandboxId
     *
     * @return ResponseInterface
     */
    public function markAsRead(int $sandboxId): ResponseInterface
    {
        return $this->handleResponse($this->httpPatch(
            sprintf('%s/api/accounts/%s/inboxes/%s/all_read', $this->getHost(), $this->getAccountId(), $sandboxId)
        ));
    }

    /**
     * Reset SMTP credentials of the sandbox.
     *
     * @param int $sandboxId
     *
     * @return ResponseInterface
     */
    public function resetSmtpCredentials(int $sandboxId): ResponseInterface
    {
        return $this->handleResponse($this->httpPatch(
            sprintf('%s/api/accounts/%s/inboxes/%s/reset_credentials', $this->getHost(), $this->getAccountId(), $sandboxId)
        ));
    }

    /**
     * Turn the email address of the sandbox on/off.
     *
     * @param int $sandboxId
     *
     * @return ResponseInterface
     */
    public function toggleEmailAddress(int $sandboxId): ResponseInterface
    {
        return $this->handleResponse($this->httpPatch(
            sprintf('%s/api/accounts/%s/inboxes/%s/toggle_email_username', $this->getHost(), $this->getAccountId(), $sandboxId)
        ));
    }

    /**
     * Reset username of email address per sandbox.
     *
     * @param int $sandboxId
     *
     * @return ResponseInterface
     */
    public function resetEmailAddress(int $sandboxId): ResponseInterface
    {
        return $this->handleResponse($this->httpPatch(
            sprintf('%s/api/accounts/%s/inboxes/%s/reset_email_username', $this->getHost(), $this->getAccountId(), $sandboxId)
        ));
    }

    private function getUpdatePayload(SandboxRequest $sandbox): array
    {
        $result = $sandbox->toArray();
        if (empty($result)) {
            throw new RuntimeException('At least one sandbox parameter should be populated ("name" or "email_username")');
        }

        return $result;
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }
}
