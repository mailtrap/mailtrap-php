<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\TrackingOptOut;

/**
 * Class CreateTrackingOptOut
 *
 * Adds an email address to the tracking opt-out list for a sending domain.
 */
final class CreateTrackingOptOut implements TrackingOptOutInterface
{
    /**
     * @param string $email    Email address to opt out of tracking
     * @param int    $domainId ID of the sending domain the opt-out applies to
     */
    public function __construct(
        private string $email,
        private int $domainId,
    ) {
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'domain_id' => $this->domainId,
        ];
    }
}
