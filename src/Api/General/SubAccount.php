<?php

declare(strict_types=1);

namespace Mailtrap\Api\General;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SubAccount
 */
class SubAccount extends AbstractApi implements GeneralInterface
{
    public function __construct(ConfigInterface $config, private int $organizationId)
    {
        parent::__construct($config);
    }

    /**
     * Get a list of sub-accounts in the organization.
     *
     * @return ResponseInterface
     */
    public function getSubAccounts(): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath())
        );
    }

    /**
     * Create a new sub-account in the organization.
     *
     * @param string $name
     * @return ResponseInterface
     */
    public function createSubAccount(string $name): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost(
                path: $this->getBasePath(),
                body: ['account' => ['name' => $name]]
            )
        );
    }

    /**
     * Delete a sub-account by ID. Requires sub-account management permissions for the organization.
     * The deletion is permanent and removes all sub-account data; deleting the organization's last
     * sub-account also deletes the organization. A repeated call for the same ID returns 404.
     * Rate limit: 10 requests per minute per organization.
     *
     * @param int $subAccountId
     * @return ResponseInterface
     */
    public function deleteSubAccount(int $subAccountId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpDelete($this->getBasePath() . '/' . $subAccountId)
        );
    }

    public function getOrganizationId(): int
    {
        return $this->organizationId;
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/organizations/%s/sub_accounts', $this->getHost(), $this->organizationId);
    }
}
