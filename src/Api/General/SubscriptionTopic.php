<?php

declare(strict_types=1);

namespace Mailtrap\Api\General;

use Mailtrap\Api\AbstractApi;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SubscriptionTopic
 *
 * Subscription Topics API: the topics contacts can subscribe to or unsubscribe from. This
 * resource is token-scoped: the account is resolved from the API token, so it takes no
 * account id. An account without Subscription Topics gets `403 Forbidden`.
 *
 * A topic is `{ id, name, description, domain_id, deleted_at, created_at, updated_at }`;
 * `domain_id` is null for a topic that covers every domain of the account.
 */
class SubscriptionTopic extends AbstractApi implements GeneralInterface
{
    /**
     * Get a paginated list of subscription topics, leaving out deleted ones.
     * The response is wrapped in `{ data: [...], pagination }`: pass `pagination.next_token`
     * with the same `$perPage` to get the next page.
     *
     * @param int|null $perPage Number of topics per page (max 100, default 50)
     * @param int|null $token   Page number to retrieve (page-token pagination, default 1)
     *
     * @return ResponseInterface
     */
    public function getSubscriptionTopics(?int $perPage = null, ?int $token = null): ResponseInterface
    {
        $parameters = [];

        if ($perPage !== null) {
            $parameters['per_page'] = $perPage;
        }

        if ($token !== null) {
            $parameters['token'] = $token;
        }

        return $this->handleResponse(
            $this->httpGet($this->getBasePath(), $parameters)
        );
    }

    /**
     * Get a subscription topic by ID, wrapped in `data`. A deleted topic is returned too,
     * with `deleted_at` set.
     *
     * @param int $subscriptionTopicId
     * @return ResponseInterface
     */
    public function getSubscriptionTopic(int $subscriptionTopicId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath() . '/' . $subscriptionTopicId)
        );
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/subscription_topics', $this->getHost());
    }
}
