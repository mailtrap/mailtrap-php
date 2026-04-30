<?php

declare(strict_types=1);

namespace Mailtrap\Api\Organization;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SubAccount
 */
class SubAccount extends AbstractApi implements OrganizationInterface
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

    private function getBasePath(): string
    {
        return sprintf('%s/api/organizations/%s/sub_accounts', $this->getHost(), $this->organizationId);
    }
}
