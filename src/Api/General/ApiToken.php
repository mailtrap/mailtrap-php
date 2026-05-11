<?php

declare(strict_types=1);

namespace Mailtrap\Api\General;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Mailtrap\DTO\Request\Permission\Permissions;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ApiToken
 */
class ApiToken extends AbstractApi implements GeneralInterface
{
    public function __construct(ConfigInterface $config, private int $accountId)
    {
        parent::__construct($config);
    }

    /**
     * Get a list of API tokens for the account.
     *
     * @return ResponseInterface
     */
    public function getApiTokens(): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath())
        );
    }

    /**
     * Get an API token by ID.
     *
     * @param int $apiTokenId
     * @return ResponseInterface
     */
    public function getApiToken(int $apiTokenId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath() . '/' . $apiTokenId)
        );
    }

    /**
     * Create a new API token. The full token value is returned only in this response.
     *
     * @param string      $name
     * @param Permissions $permissions
     *
     * @return ResponseInterface
     */
    public function createApiToken(string $name, Permissions $permissions): ResponseInterface
    {
        return $this->handleResponse($this->httpPost(
            path: $this->getBasePath(),
            body: [
                'name' => $name,
                'resources' => $permissions->toPayload(),
            ]
        ));
    }

    /**
     * Delete an API token by ID.
     *
     * @param int $apiTokenId
     * @return ResponseInterface
     */
    public function deleteApiToken(int $apiTokenId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpDelete($this->getBasePath() . '/' . $apiTokenId)
        );
    }

    /**
     * Reset an API token by ID. Returns a new token value; the previous value stops working.
     *
     * @param int $apiTokenId
     * @return ResponseInterface
     */
    public function resetApiToken(int $apiTokenId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost($this->getBasePath() . '/' . $apiTokenId . '/reset')
        );
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/accounts/%s/api_tokens', $this->getHost(), $this->accountId);
    }
}
