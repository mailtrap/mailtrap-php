<?php

declare(strict_types=1);

namespace Mailtrap\Api\General;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Organization\SubAccount;
use Mailtrap\ConfigInterface;

/**
 * Class Organization
 *
 * Intermediate entry point exposing organization-scoped resources
 * (e.g. sub-accounts).
 */
class Organization extends AbstractApi implements GeneralInterface
{
    public function __construct(ConfigInterface $config, private int $organizationId)
    {
        parent::__construct($config);
    }

    public function subAccounts(): SubAccount
    {
        return new SubAccount($this->config, $this->organizationId);
    }

    public function getOrganizationId(): int
    {
        return $this->organizationId;
    }
}
