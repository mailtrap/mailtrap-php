<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Suppression;

/**
 * Class SuppressionsFilter
 *
 * Query filters for listing suppressions. All fields are optional.
 */
final class SuppressionsFilter implements SuppressionInterface
{
    /**
     * @param string|null $email     Filter by exact email address (case-insensitive)
     * @param string|null $startTime Only suppressions created at or after this ISO 8601 timestamp
     * @param string|null $endTime   Only suppressions created at or before this ISO 8601 timestamp
     * @param string|null $lastId    Cursor: returns suppressions after this UUID
     * @param bool|null   $includeTopicUnsubscriptions Also return contacts' subscription topic
     *                                   unsubscriptions, as Suppression::TYPE_TOPIC_UNSUBSCRIPTION rows
     *                                   with their `subscription_topic_id`. Needs a token that can read
     *                                   contacts; accounts without topics have no such rows
     */
    public function __construct(
        private ?string $email = null,
        private ?string $startTime = null,
        private ?string $endTime = null,
        private ?string $lastId = null,
        private ?bool $includeTopicUnsubscriptions = null,
    ) {
    }

    public function toArray(): array
    {
        $payload = [];

        if ($this->email !== null) {
            $payload['email'] = $this->email;
        }

        if ($this->startTime !== null) {
            $payload['start_time'] = $this->startTime;
        }

        if ($this->endTime !== null) {
            $payload['end_time'] = $this->endTime;
        }

        if ($this->lastId !== null) {
            $payload['last_id'] = $this->lastId;
        }

        if ($this->includeTopicUnsubscriptions === true) {
            // http_build_query would send 1; the API opts in only on the string "true"
            $payload['include_topic_unsubscriptions'] = 'true';
        }

        return $payload;
    }
}
