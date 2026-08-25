<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Domain;

use Mailtrap\Exception\InvalidArgumentException;

/**
 * Class UpdateDomain
 *
 * Configuration settings for a sending domain. Only the fields provided are
 * updated.
 */
final class UpdateDomain implements DomainInterface
{
    /**
     * @param bool|null $openTrackingEnabled        Track opens on emails sent from this domain
     * @param bool|null $clickTrackingEnabled       Track clicks on links in emails sent from this domain
     * @param bool|null $trackingOptOutEnabled      Add the tracking opt-out link to tracked emails.
     *                                              Requires open or click tracking to be enabled.
     * @param bool|null $autoUnsubscribeLinkEnabled Automatically add an unsubscribe link to emails
     * @param bool|null $inboundEnabled             Enable inbound email for this domain so it can be
     *                                              attached to an inbound inbox as a catch-all
     */
    public function __construct(
        private ?bool $openTrackingEnabled = null,
        private ?bool $clickTrackingEnabled = null,
        private ?bool $trackingOptOutEnabled = null,
        private ?bool $autoUnsubscribeLinkEnabled = null,
        private ?bool $inboundEnabled = null,
    ) {
    }

    public function toArray(): array
    {
        $payload = [];

        if ($this->openTrackingEnabled !== null) {
            $payload['open_tracking_enabled'] = $this->openTrackingEnabled;
        }

        if ($this->clickTrackingEnabled !== null) {
            $payload['click_tracking_enabled'] = $this->clickTrackingEnabled;
        }

        if ($this->trackingOptOutEnabled !== null) {
            $payload['tracking_opt_out_enabled'] = $this->trackingOptOutEnabled;
        }

        if ($this->autoUnsubscribeLinkEnabled !== null) {
            $payload['auto_unsubscribe_link_enabled'] = $this->autoUnsubscribeLinkEnabled;
        }

        if ($this->inboundEnabled !== null) {
            $payload['inbound_enabled'] = $this->inboundEnabled;
        }

        if ($payload === []) {
            throw new InvalidArgumentException(
                'At least one updatable field must be provided to update a sending domain'
            );
        }

        return $payload;
    }
}
