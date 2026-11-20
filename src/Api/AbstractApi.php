<?php

declare(strict_types=1);

namespace Mailtrap\Api;

use Mailtrap\ConfigInterface;
use Mailtrap\Exception\HttpClientException;
use Mailtrap\Exception\HttpException;
use Mailtrap\Exception\HttpServerException;
use Mailtrap\Exception\InvalidArgumentException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AbstractApi
 */
abstract class AbstractApi
{
    public const DEFAULT_HOST = 'mailtrap.io';
    public const SENDMAIL_TRANSACTIONAL_HOST = 'send.api.mailtrap.io';
    public const SENDMAIL_SANDBOX_HOST = 'sandbox.api.mailtrap.io';
    public const SENDMAIL_BULK_HOST = 'bulk.api.mailtrap.io';

    protected ConfigInterface $config;
    protected ClientInterface $httpClient;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $this->httpClient = $this->config->getHttpClientBuilder()->getHttpClient();
    }

    protected function httpGet(string $path, array $parameters = [], array $requestHeaders = []): ResponseInterface
    {
        if (count($parameters) > 0) {
            $path .= '?' . $this->normalizeArrayParams($parameters);
        }

        return $this->httpClient->get($this->addDefaultScheme($path), $requestHeaders);
    }

    protected function httpPost(string $path, array $requestHeaders = [], ?array $body = null): ResponseInterface
    {
        return $this->httpClient->post(
            $this->addDefaultScheme($path),
            $requestHeaders,
            !empty($body) ? $this->jsonEncode($body) : null
        );
    }

    protected function httpPut(string $path, array $requestHeaders = [], ?array $body = null): ResponseInterface
    {
        return $this->httpClient->put(
            $this->addDefaultScheme($path),
            $requestHeaders,
            !empty($body) ? $this->jsonEncode($body) : null
        );
    }

    protected function httpPatch(string $path, array $requestHeaders = [], ?array $body = null): ResponseInterface
    {
        return $this->httpClient->patch(
            $this->addDefaultScheme($path),
            $requestHeaders,
            !empty($body) ? $this->jsonEncode($body) : null
        );
    }

    protected function httpDelete(string $path, array $requestHeaders = [], ?array $body = null): ResponseInterface
    {
        return $this->httpClient->delete(
            $this->addDefaultScheme($path),
            $requestHeaders,
            !empty($body) ? $this->jsonEncode($body) : null
        );
    }

    protected function getHost(): string
    {
        return $this->config->getHost() ?: self::DEFAULT_HOST;
    }

    protected function handleResponse(ResponseInterface $response): ResponseInterface
    {
        if (!$this->config->isResponseThrowOnError()) {
            return $response;
        }

        $statusCode = $response->getStatusCode();
        switch (true) {
            case $statusCode >= 200 && $statusCode < 300:
                // Everything fine
                break;
            case $statusCode >= 400 && $statusCode < 500:
                throw HttpClientException::createFromResponse($response);
            case $statusCode >= 500:
                throw HttpServerException::createFromResponse($response);
            default:
                throw new HttpException(
                    sprintf('An unexpected error occurred. HTTP response code ("%d") received.', $statusCode),
                    $statusCode
                );

        }

        return $response;
    }

    /**
     * @param mixed    $value
     * @param int|null $flags
     * @param int      $maxDepth
     *
     * @return string
     */
    private function jsonEncode(mixed $value, ?int $flags = null, int $maxDepth = 512): string
    {
        $flags ??= \JSON_HEX_TAG | \JSON_HEX_APOS | \JSON_HEX_AMP | \JSON_HEX_QUOT | \JSON_PRESERVE_ZERO_FRACTION;

        try {
            $value = json_encode($value, $flags | \JSON_THROW_ON_ERROR, $maxDepth);
        } catch (\JsonException $e) {
            throw new InvalidArgumentException('Invalid value for "json" option: ' . $e->getMessage());
        }

        return $value;
    }

    private function addDefaultScheme(string $path): string
    {
        return empty(parse_url($path, PHP_URL_SCHEME)) ? 'https://' . $path : $path;
    }

    /**
     * Mailtrap API doesn't support array numeric values in GET params like that - inbox_ids[0]=1001&inbox_ids[1]=2002
     * that's why we need to do some normalization to use without numbers inbox_ids[]=1001&inbox_ids[]=2002
     *
     * @param array $parameters
     *
     * @return string
     */
    private function normalizeArrayParams(array $parameters): string
    {
        return preg_replace('/%5B\d+%5D/imU', '%5B%5D', http_build_query($parameters, '', '&'));
    }
}
