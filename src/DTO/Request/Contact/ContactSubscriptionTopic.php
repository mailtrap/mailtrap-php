<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Contact;

use Mailtrap\DTO\Request\RequestInterface;
use Mailtrap\Exception\InvalidArgumentException;

/**
 * A contact's status for one subscription topic on one domain, as sent in a contact create,
 * update or import. Contacts responses return the same entries in `subscription_topics`.
 */
final class ContactSubscriptionTopic implements RequestInterface
{
    public const STATUS_SUBSCRIBED = 'subscribed';
    public const STATUS_UNSUBSCRIBED = 'unsubscribed';

    /**
     * @param int    $subscriptionTopicId ID of the subscription topic, as returned by the Subscription Topics API
     * @param int    $domainId            ID of the sending domain, as returned by the Domains API
     * @param string $status              One of ContactSubscriptionTopic::STATUS_*
     */
    public function __construct(
        private int $subscriptionTopicId,
        private int $domainId,
        private string $status
    ) {
    }

    public static function init(int $subscriptionTopicId, int $domainId, string $status): self
    {
        return new self($subscriptionTopicId, $domainId, $status);
    }

    /**
     * Serializes a contact's topic entries, so each DTO that carries them shares one check.
     * Null stays null, for the DTOs to leave `subscription_topics` out of the request.
     *
     * @param ContactSubscriptionTopic[]|null $subscriptionTopics
     * @return array<int, array<string, int|string>>|null
     */
    public static function listToArray(?array $subscriptionTopics): ?array
    {
        if ($subscriptionTopics === null) {
            return null;
        }

        return array_map(
            static function ($subscriptionTopic): array {
                if (!$subscriptionTopic instanceof self) {
                    throw new InvalidArgumentException(
                        'Each subscription topic must be an instance of ContactSubscriptionTopic.'
                    );
                }

                return $subscriptionTopic->toArray();
            },
            array_values($subscriptionTopics)
        );
    }

    public function getSubscriptionTopicId(): int
    {
        return $this->subscriptionTopicId;
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function toArray(): array
    {
        return [
            'subscription_topic_id' => $this->getSubscriptionTopicId(),
            'domain_id' => $this->getDomainId(),
            'status' => $this->getStatus(),
        ];
    }
}
