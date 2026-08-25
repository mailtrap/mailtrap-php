<?php

declare(strict_types=1);

namespace Mailtrap\Api\Sending;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Mailtrap\DTO\Request\Domain\CreateCompanyInfo;
use Mailtrap\DTO\Request\Domain\UpdateCompanyInfo;
use Psr\Http\Message\ResponseInterface;

/**
 * Class CompanyInfo
 */
class CompanyInfo extends AbstractApi implements SendingInterface
{
    public function __construct(ConfigInterface $config, private int $domainId)
    {
        parent::__construct($config);
    }

    /**
     * Get the company info associated with the sending domain.
     *
     * @return ResponseInterface
     */
    public function getCompanyInfo(): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath())
        );
    }

    /**
     * Create the company info for the sending domain. Company info is required
     * for domain compliance verification.
     *
     * @param CreateCompanyInfo $companyInfo
     * @return ResponseInterface
     */
    public function createCompanyInfo(CreateCompanyInfo $companyInfo): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost(
                path: $this->getBasePath(),
                body: [
                    'company_info' => $companyInfo->toArray()
                ]
            )
        );
    }

    /**
     * Update the company info for the sending domain.
     *
     * @param UpdateCompanyInfo $companyInfo
     * @return ResponseInterface
     */
    public function updateCompanyInfo(UpdateCompanyInfo $companyInfo): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPatch(
                path: $this->getBasePath(),
                body: [
                    'company_info' => $companyInfo->toArray()
                ]
            )
        );
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/domains/%s/company_info', $this->getHost(), $this->domainId);
    }
}
