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
     */
    public function __construct(
        private ?string $email = null,
        private ?string $startTime = null,
        private ?string $endTime = null,
        private ?string $lastId = null,
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

        return $payload;
    }
}
