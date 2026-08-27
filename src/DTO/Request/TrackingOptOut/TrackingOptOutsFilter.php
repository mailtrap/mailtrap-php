<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\TrackingOptOut;

/**
 * Class TrackingOptOutsFilter
 *
 * Query filters for listing tracking opt-outs. All fields are optional.
 */
final class TrackingOptOutsFilter implements TrackingOptOutInterface
{
    /**
     * @param string|null $email     Filter by exact email address (case-insensitive)
     * @param string|null $startTime Only opt-outs created at or after this ISO 8601 timestamp
     * @param string|null $endTime   Only opt-outs created at or before this ISO 8601 timestamp
     * @param string|null $lastId    Cursor from the previous response's last_id
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
