<?php

declare(strict_types=1);

namespace Mailtrap\Api\Sending;

use Mailtrap\Api\AbstractApi;
use Mailtrap\DTO\Request\TrackingOptOut\CreateTrackingOptOut;
use Mailtrap\DTO\Request\TrackingOptOut\TrackingOptOutsFilter;
use Psr\Http\Message\ResponseInterface;

/**
 * Class TrackingOptOut
 */
class TrackingOptOut extends AbstractApi implements SendingInterface
{
    /**
     * List email addresses that have opted out of open and click tracking.
     * The endpoint returns up to 1000 records per request; pass the previous
     * response's last_id to fetch the next page.
     *
     * @param TrackingOptOutsFilter|null $filter
     * @return ResponseInterface
     */
    public function getTrackingOptOuts(?TrackingOptOutsFilter $filter = null): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet(
                $this->getBasePath(),
                $filter ? $filter->toArray() : []
            )
        );
    }

    /**
     * Add an email address to the tracking opt-out list for a sending domain.
     *
     * @param CreateTrackingOptOut $trackingOptOut
     * @return ResponseInterface
     */
    public function createTrackingOptOut(CreateTrackingOptOut $trackingOptOut): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost(
                path: $this->getBasePath(),
                body: $trackingOptOut->toArray()
            )
        );
    }

    /**
     * Remove an email address from the tracking opt-out list so open and click
     * tracking can apply again.
     *
     * @param string $trackingOptOutId
     * @return ResponseInterface
     */
    public function deleteTrackingOptOut(string $trackingOptOutId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpDelete(
                sprintf('%s/%s', $this->getBasePath(), $trackingOptOutId)
            )
        );
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/tracking_opt_outs', $this->getHost());
    }
}
