<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Suppression;

/**
 * Class CreateSuppression
 *
 * Adds an email address to the account's suppression list.
 */
final class CreateSuppression implements SuppressionInterface
{
    /**
     * @param string      $email         Email address to suppress
     * @param int         $domainId      ID of the domain to suppress this email for
     * @param string      $sendingStream One of Suppression::SENDING_STREAM_*
     * @param string|null $type          One of Suppression::TYPE_*. The API defaults to
     *                                   "manual import" when omitted.
     */
    public function __construct(
        private string $email,
        private int $domainId,
        private string $sendingStream,
        private ?string $type = null,
    ) {
    }

    public function toArray(): array
    {
        $payload = [
            'email' => $this->email,
            'domain_id' => $this->domainId,
            'sending_stream' => $this->sendingStream,
        ];

        if ($this->type !== null) {
            $payload['type'] = $this->type;
        }

        return $payload;
    }
}
