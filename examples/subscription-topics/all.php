<?php

declare(strict_types=1);

use Mailtrap\Config;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapGeneralClient;

require __DIR__ . '/../../vendor/autoload.php';

$config = new Config($_ENV['MAILTRAP_API_KEY']); // your API token from https://mailtrap.io/api-tokens
$subscriptionTopics = (new MailtrapGeneralClient($config))->subscriptionTopics();

/**
 * Get a paginated list of subscription topics; deleted topics are left out.
 * Optional: $perPage (max 100, default 50) and $token (page number).
 *
 * The response is wrapped in `{ data: [...], pagination }`. A topic's `domain_id` is null when
 * it covers every domain of the account.
 *
 * GET https://mailtrap.io/api/subscription_topics
 */
try {
    $topics = ResponseHelper::toArray($subscriptionTopics->getSubscriptionTopics(perPage: 50));

    var_dump($topics);

    // pass `pagination.next_token` with the same $perPage to get the next page
    $nextToken = $topics['pagination']['next_token'] ?? null;
    if ($nextToken !== null) {
        var_dump(ResponseHelper::toArray($subscriptionTopics->getSubscriptionTopics(perPage: 50, token: $nextToken)));
    }
} catch (Exception $e) {
    echo $e->getCode() === 403
        ? 'Subscription Topics are not available for this account.'
        : 'Caught exception: ' . $e->getMessage();
    echo PHP_EOL;

    exit(1);
}

/**
 * Get a subscription topic by ID (wrapped in `data`). A deleted topic is returned too, with
 * `deleted_at` set.
 *
 * GET https://mailtrap.io/api/subscription_topics/{subscription_topic_id}
 */
try {
    $subscriptionTopicId = $topics['data'][0]['id'] ?? null;

    if ($subscriptionTopicId !== null) {
        $response = $subscriptionTopics->getSubscriptionTopic($subscriptionTopicId);

        var_dump(ResponseHelper::toArray($response));
    } else {
        echo 'No subscription topics yet: create one in Mailtrap first.', PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}
