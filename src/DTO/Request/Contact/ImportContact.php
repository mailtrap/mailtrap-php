<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Contact;

/**
 * Class ImportContact
 */
final class ImportContact implements ContactInterface
{
    /**
     * @param string                          $email
     * @param array                           $fields
     * @param int[]                           $listIdsIncluded
     * @param int[]                           $listIdsExcluded
     * @param ContactSubscriptionTopic[]|null $subscriptionTopics Topic statuses per domain; the topic and domain
     *                                                            pairs left out keep their status, and null leaves
     *                                                            them all out. Accounts without topics reject the
     *                                                            whole import
     */
    public function __construct(
        private string $email,
        private array $fields = [],
        private array $listIdsIncluded = [],
        private array $listIdsExcluded = [],
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
        ?array $subscriptionTopics = null
    ): self {
        return new self($email, $fields, $listIdsIncluded, $listIdsExcluded, $subscriptionTopics);
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

    /**
     * @return ContactSubscriptionTopic[]|null
     */
    public function getSubscriptionTopics(): ?array
    {
        return $this->subscriptionTopics;
    }

    public function toArray(): array
    {
        $payload = [
            'email'                => $this->getEmail(),
            'fields'               => $this->getFields(),
            'list_ids_included'    => $this->getListIdsIncluded(),
            'list_ids_excluded'    => $this->getListIdsExcluded(),
        ];

        if ($this->getSubscriptionTopics() !== null) {
            $payload['subscription_topics'] = ContactSubscriptionTopic::listToArray($this->getSubscriptionTopics());
        }

        return $payload;
    }
}
