<?php

declare(strict_types=1);

namespace Mailtrap\Api\Sending;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Stats
 */
class Stats extends AbstractApi implements SendingInterface
{
    public function __construct(ConfigInterface $config, private int $accountId)
    {
        parent::__construct($config);
    }

    /**
     * Get aggregated sending stats.
     *
     * @param string $startDate
     * @param string $endDate
     * @param array  $sendingDomainIds
     * @param array  $sendingStreams
     * @param array  $categories
     * @param array  $emailServiceProviders
     *
     * @return ResponseInterface
     */
    public function get(
        string $startDate,
        string $endDate,
        array $sendingDomainIds = [],
        array $sendingStreams = [],
        array $categories = [],
        array $emailServiceProviders = []
    ): ResponseInterface {
        return $this->handleResponse($this->httpGet(
            $this->getBasePath(),
            $this->buildQueryParams($startDate, $endDate, $sendingDomainIds, $sendingStreams, $categories, $emailServiceProviders)
        ));
    }

    /**
     * Get sending stats grouped by domains.
     *
     * @param string $startDate
     * @param string $endDate
     * @param array  $sendingDomainIds
     * @param array  $sendingStreams
     * @param array  $categories
     * @param array  $emailServiceProviders
     *
     * @return ResponseInterface
     */
    public function byDomain(
        string $startDate,
        string $endDate,
        array $sendingDomainIds = [],
        array $sendingStreams = [],
        array $categories = [],
        array $emailServiceProviders = []
    ): ResponseInterface {
        return $this->handleResponse($this->httpGet(
            $this->getBasePath() . '/domains',
            $this->buildQueryParams($startDate, $endDate, $sendingDomainIds, $sendingStreams, $categories, $emailServiceProviders)
        ));
    }

    /**
     * Get sending stats grouped by categories.
     *
     * @param string $startDate
     * @param string $endDate
     * @param array  $sendingDomainIds
     * @param array  $sendingStreams
     * @param array  $categories
     * @param array  $emailServiceProviders
     *
     * @return ResponseInterface
     */
    public function byCategory(
        string $startDate,
        string $endDate,
        array $sendingDomainIds = [],
        array $sendingStreams = [],
        array $categories = [],
        array $emailServiceProviders = []
    ): ResponseInterface {
        return $this->handleResponse($this->httpGet(
            $this->getBasePath() . '/categories',
            $this->buildQueryParams($startDate, $endDate, $sendingDomainIds, $sendingStreams, $categories, $emailServiceProviders)
        ));
    }

    /**
     * Get sending stats grouped by email service providers.
     *
     * @param string $startDate
     * @param string $endDate
     * @param array  $sendingDomainIds
     * @param array  $sendingStreams
     * @param array  $categories
     * @param array  $emailServiceProviders
     *
     * @return ResponseInterface
     */
    public function byEmailServiceProvider(
        string $startDate,
        string $endDate,
        array $sendingDomainIds = [],
        array $sendingStreams = [],
        array $categories = [],
        array $emailServiceProviders = []
    ): ResponseInterface {
        return $this->handleResponse($this->httpGet(
            $this->getBasePath() . '/email_service_providers',
            $this->buildQueryParams($startDate, $endDate, $sendingDomainIds, $sendingStreams, $categories, $emailServiceProviders)
        ));
    }

    /**
     * Get sending stats grouped by date.
     *
     * @param string $startDate
     * @param string $endDate
     * @param array  $sendingDomainIds
     * @param array  $sendingStreams
     * @param array  $categories
     * @param array  $emailServiceProviders
     *
     * @return ResponseInterface
     */
    public function byDate(
        string $startDate,
        string $endDate,
        array $sendingDomainIds = [],
        array $sendingStreams = [],
        array $categories = [],
        array $emailServiceProviders = []
    ): ResponseInterface {
        return $this->handleResponse($this->httpGet(
            $this->getBasePath() . '/date',
            $this->buildQueryParams($startDate, $endDate, $sendingDomainIds, $sendingStreams, $categories, $emailServiceProviders)
        ));
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/accounts/%s/stats', $this->getHost(), $this->getAccountId());
    }

    private function buildQueryParams(
        string $startDate,
        string $endDate,
        array $sendingDomainIds,
        array $sendingStreams,
        array $categories,
        array $emailServiceProviders
    ): array {
        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        if (count($sendingDomainIds) > 0) {
            $params['sending_domain_ids'] = $sendingDomainIds;
        }

        if (count($sendingStreams) > 0) {
            $params['sending_streams'] = $sendingStreams;
        }

        if (count($categories) > 0) {
            $params['categories'] = $categories;
        }

        if (count($emailServiceProviders) > 0) {
            $params['email_service_providers'] = $emailServiceProviders;
        }

        return $params;
    }
}
