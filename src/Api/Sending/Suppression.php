<?php

declare(strict_types=1);

namespace Mailtrap\Api\Sending;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Mailtrap\DTO\Request\Suppression\CreateSuppression;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Suppression
 */
class Suppression extends AbstractApi implements SendingInterface
{
    public function __construct(ConfigInterface $config, private int $accountId)
    {
        parent::__construct($config);
    }

    /**
     * List and search suppressions by email. The endpoint returns up to 1000 suppressions per request.
     *
     * @param string|null $email The email to filter suppressions by, or null to get all.
     * @return ResponseInterface
     */
    public function getSuppressions(?string $email = null): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet(
                $this->getBasePath(),
                $email ? ['email' => $email] : []
            )
        );
    }

    /**
     * Add an email address to the account's suppression list.
     *
     * @param CreateSuppression $suppression
     * @return ResponseInterface
     */
    public function createSuppression(CreateSuppression $suppression): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost(
                path: $this->getBasePath(),
                body: $suppression->toArray()
            )
        );
    }

    /**
     * Delete a suppression by ID (UUID).
     * Mailtrap will no longer prevent sending to this email unless it's recorded in suppressions again.
     *
     * @param string $suppressionId
     * @return ResponseInterface
     */
    public function deleteSuppression(string $suppressionId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpDelete(
                sprintf('%s/%s', $this->getBasePath(), $suppressionId)
            )
        );
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/accounts/%s/suppressions', $this->getHost(), $this->accountId);
    }
}
