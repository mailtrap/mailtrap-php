<?php

declare(strict_types=1);

namespace Mailtrap\Api\General;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Mailtrap\DTO\Request\EmailCampaign\CreateEmailCampaign;
use Mailtrap\DTO\Request\EmailCampaign\UpdateEmailCampaign;
use Psr\Http\Message\ResponseInterface;

/**
 * Class EmailCampaign
 *
 * Email Campaigns API. Note: this resource is token-scoped — the account is resolved from
 * the API token, so the path takes no account id. The account id is accepted for
 * consistency with the other General API resources only.
 */
class EmailCampaign extends AbstractApi implements GeneralInterface
{
    public function __construct(ConfigInterface $config, private int $accountId)
    {
        parent::__construct($config);
    }

    /**
     * Get a paginated list of email campaigns, newest first.
     * The response is wrapped in `{ data: [...], pagination }`.
     *
     * @param int|null    $perPage Number of campaigns per page (max 100, default 50)
     * @param string|null $search  Filter campaigns by name
     * @param int|null    $token   Page number to retrieve (page-token pagination, default 1)
     *
     * @return ResponseInterface
     */
    public function getEmailCampaigns(
        ?int $perPage = null,
        ?string $search = null,
        ?int $token = null
    ): ResponseInterface {
        $parameters = [];

        if ($perPage !== null) {
            $parameters['per_page'] = $perPage;
        }

        if ($search !== null) {
            $parameters['search'] = $search;
        }

        if ($token !== null) {
            $parameters['token'] = $token;
        }

        return $this->handleResponse(
            $this->httpGet($this->getBasePath(), $parameters)
        );
    }

    /**
     * Get an email campaign by ID. The campaign is wrapped in `data`.
     *
     * @param int $emailCampaignId
     * @return ResponseInterface
     */
    public function getEmailCampaign(int $emailCampaignId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath() . '/' . $emailCampaignId)
        );
    }

    /**
     * Create a new email campaign in the `draft` state. The campaign is wrapped in `data`.
     *
     * @param CreateEmailCampaign $emailCampaign
     * @return ResponseInterface
     */
    public function createEmailCampaign(CreateEmailCampaign $emailCampaign): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost(
                path: $this->getBasePath(),
                body: $emailCampaign->toArray()
            )
        );
    }

    /**
     * Update an existing `draft` email campaign (PATCH, partial). The campaign is wrapped
     * in `data`.
     *
     * @param int                 $emailCampaignId
     * @param UpdateEmailCampaign $emailCampaign
     * @return ResponseInterface
     */
    public function updateEmailCampaign(int $emailCampaignId, UpdateEmailCampaign $emailCampaign): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPatch(
                path: $this->getBasePath() . '/' . $emailCampaignId,
                body: $emailCampaign->toArray()
            )
        );
    }

    /**
     * Delete an email campaign. The campaign must not be in a sending state.
     * Returns `204 No Content` with an empty body.
     *
     * @param int $emailCampaignId
     * @return ResponseInterface
     */
    public function deleteEmailCampaign(int $emailCampaignId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpDelete($this->getBasePath() . '/' . $emailCampaignId)
        );
    }

    /**
     * Start sending a `draft` email campaign immediately. The campaign is wrapped in `data`.
     *
     * @param int $emailCampaignId
     * @return ResponseInterface
     */
    public function startEmailCampaign(int $emailCampaignId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost($this->getBasePath() . '/' . $emailCampaignId . '/start')
        );
    }

    /**
     * Schedule a `draft` email campaign to start sending at a future time (no more than
     * 1 month ahead). The scheduled time is reported back in
     * `current_state_metadata.scheduled_at`. The campaign is wrapped in `data`.
     *
     * @param int                       $emailCampaignId
     * @param string|\DateTimeInterface $datetime When to send the campaign (ISO 8601)
     * @return ResponseInterface
     */
    public function scheduleEmailCampaign(int $emailCampaignId, string|\DateTimeInterface $datetime): ResponseInterface
    {
        if ($datetime instanceof \DateTimeInterface) {
            $datetime = $datetime->format(\DateTimeInterface::ATOM);
        }

        return $this->handleResponse(
            $this->httpPost(
                path: $this->getBasePath() . '/' . $emailCampaignId . '/schedule',
                body: ['datetime' => $datetime]
            )
        );
    }

    /**
     * Cancel a `scheduled` email campaign, returning it to the `draft` state. The campaign
     * is wrapped in `data`.
     *
     * @param int $emailCampaignId
     * @return ResponseInterface
     */
    public function cancelEmailCampaign(int $emailCampaignId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost($this->getBasePath() . '/' . $emailCampaignId . '/cancel')
        );
    }

    /**
     * Terminate an email campaign that is currently sending (`started`, `queued`, or
     * `paused`), aborting the in-flight send. The campaign is wrapped in `data`.
     *
     * @param int $emailCampaignId
     * @return ResponseInterface
     */
    public function terminateEmailCampaign(int $emailCampaignId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost($this->getBasePath() . '/' . $emailCampaignId . '/terminate')
        );
    }

    /**
     * Reset a `scheduled` email campaign back to the `draft` state. The campaign is wrapped
     * in `data`.
     *
     * @param int $emailCampaignId
     * @return ResponseInterface
     */
    public function resetEmailCampaign(int $emailCampaignId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost($this->getBasePath() . '/' . $emailCampaignId . '/reset')
        );
    }

    /**
     * Get aggregated performance statistics for an email campaign, wrapped in `data`.
     * By default stats cover the whole period since the campaign was last started; narrow
     * the window with the optional date parameters.
     *
     * @param int         $emailCampaignId
     * @param string|null $startDate Start of the aggregation window (inclusive), `YYYY-MM-DD`
     * @param string|null $endDate   End of the aggregation window (inclusive), `YYYY-MM-DD`
     * @return ResponseInterface
     */
    public function getEmailCampaignStats(
        int $emailCampaignId,
        ?string $startDate = null,
        ?string $endDate = null
    ): ResponseInterface {
        $parameters = [];

        if ($startDate !== null) {
            $parameters['start_date'] = $startDate;
        }

        if ($endDate !== null) {
            $parameters['end_date'] = $endDate;
        }

        return $this->handleResponse(
            $this->httpGet($this->getBasePath() . '/' . $emailCampaignId . '/stats', $parameters)
        );
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    /**
     * Email campaigns are token-scoped: the path is bare (no `/api/accounts/{account_id}`).
     */
    private function getBasePath(): string
    {
        return sprintf('%s/api/email_campaigns', $this->getHost());
    }
}
