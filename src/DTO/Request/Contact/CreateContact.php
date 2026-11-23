<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Contact;

/**
 * Class CreateContact
 */
final class CreateContact implements ContactInterface
{
    /**
     * @param string                          $email
     * @param array                           $fields
     * @param int[]                           $listIds
     * @param ContactSubscriptionTopic[]|null $subscriptionTopics Topic statuses per domain; null leaves them out
     *                                                            of the request. Accounts without topics reject them
     */
    public function __construct(
        private string $email,
        private array $fields = [],
        private array $listIds = [],
        private ?array $subscriptionTopics = null
    ) {
    }

    /**
     * @param ContactSubscriptionTopic[]|null $subscriptionTopics
     */
    public static function init(
        string $email,
        array $fields = [],
        array $listIds = [],
        ?array $subscriptionTopics = null
    ): self {
        return new self($email, $fields, $listIds, $subscriptionTopics);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getListIds(): array
    {
        return $this->listIds;
    }

    /**
     * @return ContactSubscriptionTopic[]|null
     */
    public function getSubscriptionTopics(): ?array
    {
        return $this->subscriptionTopics;
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'email' => $this->getEmail(),
                'fields' => $this->getFields(),
                'list_ids' => $this->getListIds(),
                'subscription_topics' => ContactSubscriptionTopic::listToArray($this->getSubscriptionTopics()),
            ],
            fn($value) => $value !== null
        );
    }
}
