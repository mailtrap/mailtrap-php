<?php

declare(strict_types=1);

namespace Mailtrap\Api\General;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Mailtrap\DTO\Request\Permission\Permissions;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Permission
 */
class Permission extends AbstractApi implements GeneralInterface
{
    public function __construct(ConfigInterface $config, private int $accountId)
    {
        parent::__construct($config);
    }

    /**
     * Get all resources in your account (Inboxes, Projects, Domains, Billing and Account itself) to which the token has admin access.
     *
     * @return ResponseInterface
     */
    public function getResources(): ResponseInterface
    {
        return $this->handleResponse($this->httpGet(
            sprintf('%s/api/accounts/%s/permissions/resources', $this->getHost(), $this->getAccountId())
        ));
    }

    /**
     * Manage user or token permissions.
     * If you send a combination of resource_type and resource_id that already exists, the permission is updated.
     * If the combination doesn’t exist, the permission is created.
     *
     * @param int         $accountAccessId
     * @param Permissions $permissions
     *
     * @return ResponseInterface
     */
    public function update(int $accountAccessId, Permissions $permissions): ResponseInterface
    {
        return $this->handleResponse($this->httpPut(
            sprintf('%s/api/accounts/%s/account_accesses/%s/permissions/bulk', $this->getHost(), $this->getAccountId(), $accountAccessId),
            [],
            ['permissions' => $permissions->toPayload()]
        ));
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }
}
