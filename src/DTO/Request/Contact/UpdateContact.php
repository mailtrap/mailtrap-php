<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Contact;

/**
 * Class UpdateContact
 */
final class UpdateContact implements ContactInterface
{
    /**
     * @param string                          $email
     * @param array                           $fields
     * @param int[]                           $listIdsIncluded
     * @param int[]                           $listIdsExcluded
     * @param bool|null                       $unsubscribed
     * @param ContactSubscriptionTopic[]|null $subscriptionTopics Topic statuses per domain; the topic and domain
     *                                                            pairs left out keep their status, and null leaves
     *                                                            them all out. Accounts without topics reject them
     */
    public function __construct(
        private string $email,
        private array $fields = [],
        private array $listIdsIncluded = [],
        private array $listIdsExcluded = [],
        private ?bool $unsubscribed = null,
        private ?array $subscriptionTopics = null
    ) {
    }

    /**
     * @param ContactSubscriptionTopic[]|null $subscriptionTopics
     */
    public static function init(
        string $email,
        array $fields = [],
        array $listIdsIncluded = [],
        array $listIdsExcluded = [],
        ?bool $unsubscribed = null,
        ?array $subscriptionTopics = null
    ): self {
        return new self($email, $fields, $listIdsIncluded, $listIdsExcluded, $unsubscribed, $subscriptionTopics);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getListIdsIncluded(): array
    {
        return $this->listIdsIncluded;
    }

    public function getListIdsExcluded(): array
    {
        return $this->listIdsExcluded;
    }

    public function getUnsubscribed(): ?bool
    {
        return $this->unsubscribed;
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
                'email'                => $this->getEmail(),
                'fields'               => $this->getFields(),
                'list_ids_included'    => $this->getListIdsIncluded(),
                'list_ids_excluded'    => $this->getListIdsExcluded(),
                'unsubscribed'         => $this->getUnsubscribed(),
                'subscription_topics'  => ContactSubscriptionTopic::listToArray($this->getSubscriptionTopics()),
            ],
            fn($value) => $value !== null
        );
    }
}
