<?php

declare(strict_types=1);

namespace Mailtrap\Api\General;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Mailtrap\DTO\Request\ApiToken\TokenExpiration;
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
     * @param string               $name
     * @param Permissions          $permissions
     * @param TokenExpiration|null $expiration Optional token expiration as an ISO 8601 date-time.
     *                                         Omit for the server default (a 1-year default is being rolled out).
     *                                         Use TokenExpiration::never() for a token that never expires.
     *                                         Past or more-than-5-years-ahead values are rejected with 422.
     *
     * @return ResponseInterface
     */
    public function createApiToken(string $name, Permissions $permissions, ?TokenExpiration $expiration = null): ResponseInterface
    {
        $body = [
            'name' => $name,
            'resources' => $permissions->toPayload(),
        ];

        if ($expiration !== null) {
            $body['expires_at'] = $expiration->getValue();
        }

        return $this->handleResponse($this->httpPost(
            path: $this->getBasePath(),
            body: $body
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
     * @param int                  $apiTokenId
     * @param TokenExpiration|null $expiration Optional expiration of the new token as an ISO 8601 date-time.
     *                                         Omit for the server default (a 1-year default is being rolled out).
     *                                         Use TokenExpiration::never() for a token that never expires.
     *                                         Past or more-than-5-years-ahead values are rejected with 422.
     *
     * @return ResponseInterface
     */
    public function resetApiToken(int $apiTokenId, ?TokenExpiration $expiration = null): ResponseInterface
    {
        $path = $this->getBasePath() . '/' . $apiTokenId . '/reset';

        if ($expiration === null) {
            return $this->handleResponse(
                $this->httpPost($path)
            );
        }

        return $this->handleResponse($this->httpPost(
            path: $path,
            body: ['expires_at' => $expiration->getValue()]
        ));
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/accounts/%s/api_tokens', $this->getHost(), $this->accountId);
    }
}
