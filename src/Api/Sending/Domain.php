<?php

declare(strict_types=1);

namespace Mailtrap\Api\Sending;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Mailtrap\DTO\Request\Domain\UpdateDomain;
use Mailtrap\Helper\Deprecation;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Domain
 */
class Domain extends AbstractApi implements SendingInterface
{
    public function __construct(ConfigInterface $config, private int $accountId)
    {
        parent::__construct($config);
    }

    /**
     * Get a list of domains.
     *
     * @return ResponseInterface
     */
    public function getDomains(): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath())
        );
    }

    /**
     * Create a new domain.
     *
     * @param string $domainName
     * @return ResponseInterface
     */
    public function createDomain(string $domainName): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost(
                path: $this->getBasePath(),
                body: [
                    'sending_domain' => [
                      'domain_name' => $domainName
                    ]
                ]
            )
        );
    }

    /**
     * Get a domain by ID.
     *
     * @param int $domainId
     * @return ResponseInterface
     */
    public function getDomainById(int $domainId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet(
                sprintf('%s/%s', $this->getBasePath(), $domainId)
            )
        );
    }

    /**
     * Update configuration settings for a domain.
     *
     * @param int          $domainId
     * @param UpdateDomain $domain
     * @return ResponseInterface
     */
    public function updateDomain(int $domainId, UpdateDomain $domain): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPatch(
                path: sprintf('%s/%s', $this->getBasePath(), $domainId),
                body: [
                    'sending_domain' => $domain->toArray()
                ]
            )
        );
    }

    /**
     * Delete a domain by ID.
     *
     * @param int $domainId
     * @return ResponseInterface
     */
    public function deleteDomain(int $domainId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpDelete(
                sprintf('%s/%s', $this->getBasePath(), $domainId)
            )
        );
    }

    /**
     * Send domain setup instructions.
     *
     * @param int $domainId
     * @param string $email
     * @return ResponseInterface
     */
    public function sendDomainSetupInstructions(int $domainId, string $email): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost(
                path: sprintf('%s/%s/send_setup_instructions', $this->getBasePath(), $domainId),
                body: [
                    'email' => $email
                ]
            )
        );
    }

    /**
     * @deprecated Use getDomains() instead.
     */
    public function getSendingDomains(): ResponseInterface
    {
        Deprecation::trigger(self::class . '::getSendingDomains()', self::class . '::getDomains()');

        return $this->getDomains();
    }

    /**
     * @deprecated Use createDomain() instead.
     */
    public function createSendingDomain(string $domainName): ResponseInterface
    {
        Deprecation::trigger(self::class . '::createSendingDomain()', self::class . '::createDomain()');

        return $this->createDomain($domainName);
    }

    /**
     * @deprecated Use updateDomain() instead.
     */
    public function updateSendingDomain(int $domainId, UpdateDomain $domain): ResponseInterface
    {
        Deprecation::trigger(self::class . '::updateSendingDomain()', self::class . '::updateDomain()');

        return $this->updateDomain($domainId, $domain);
    }

    /**
     * @deprecated Use deleteDomain() instead.
     */
    public function deleteSendingDomain(int $domainId): ResponseInterface
    {
        Deprecation::trigger(self::class . '::deleteSendingDomain()', self::class . '::deleteDomain()');

        return $this->deleteDomain($domainId);
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/accounts/%s/sending_domains', $this->getHost(), $this->accountId);
    }
}
