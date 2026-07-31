<?php

declare(strict_types=1);

use Mailtrap\Config;
use Mailtrap\DTO\Request\EmailCampaign\CreateEmailCampaign;
use Mailtrap\DTO\Request\EmailCampaign\EmailCampaignInterface;
use Mailtrap\DTO\Request\EmailCampaign\ReplyTo;
use Mailtrap\DTO\Request\EmailCampaign\TemplateAttributes;
use Mailtrap\DTO\Request\EmailCampaign\UpdateEmailCampaign;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapGeneralClient;

require __DIR__ . '/../../vendor/autoload.php';

$accountId = (int) $_ENV['MAILTRAP_ACCOUNT_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']); // your API token from https://mailtrap.io/api-tokens
$emailCampaigns = (new MailtrapGeneralClient($config))->emailCampaigns($accountId);

/**
 * Get a paginated list of email campaigns (newest first).
 * Optional filters: $perPage (max 100, default 50), $search (name filter), $token (page number).
 *
 * The response is wrapped in `{ data: [...], pagination }`.
 *
 * GET https://mailtrap.io/api/email_campaigns
 */
try {
    $response = $emailCampaigns->getEmailCampaigns(perPage: 50, search: 'Spring', token: 1);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}

/**
 * Create a new email campaign in the `draft` state (wrapped in `data`).
 * `name`, `domain_id`, `from_local_part` and a template `subject` are required.
 *
 * POST https://mailtrap.io/api/email_campaigns
 */
try {
    $response = $emailCampaigns->createEmailCampaign(new CreateEmailCampaign(
        name: 'Spring Sale',
        domainId: (int) $_ENV['MAILTRAP_DOMAIN_ID'], // set this environment variable with your sending domain ID
        fromLocalPart: 'news',
        templateAttributes: new TemplateAttributes(subject: 'Spring is here — 30% off'),
        fromDisplayName: 'Acme Marketing',
        replyTo: new ReplyTo(
            displayName: 'Acme Support',
            localPart: 'support',
            domain: 'acme.com',
        ),
    ));

    $emailCampaign = ResponseHelper::toArray($response);
    $emailCampaignId = $emailCampaign['data']['id']; // reused by all the examples below

    var_dump($emailCampaign);
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;

    exit(1); // the examples below operate on the campaign created here
}

/**
 * Get a single email campaign by ID (wrapped in `data`).
 *
 * GET https://mailtrap.io/api/email_campaigns/{email_campaign_id}
 */
try {
    $response = $emailCampaigns->getEmailCampaign($emailCampaignId);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}

/**
 * Update an existing `draft` email campaign (PATCH — only provided attributes change).
 * Add the design and pick the audience; `contact_list_ids`/`contact_segment_ids` are the
 * full set of included lists/segments (`[]` clears them).
 *
 * PATCH https://mailtrap.io/api/email_campaigns/{email_campaign_id}
 */
try {
    $response = $emailCampaigns->updateEmailCampaign(
        $emailCampaignId,
        new UpdateEmailCampaign(
            name: 'Spring Sale (updated)',
            deliveryMode: EmailCampaignInterface::DELIVERY_MODE_GRADUAL,
            deliveryOptions: ['emails_per_hour' => 1000],
            templateAttributes: new TemplateAttributes(
                subject: 'New subject',
                bodyHtml: '<html><body><h1>Hi {{first_name}}!</h1>'
                    . '<p><a href="__unsubscribe_url__">Unsubscribe</a></p></body></html>',
                mergeTags: ['first_name'],
            ),
            contactListIds: [55, 56], // replace with your contact list IDs
            contactSegmentIds: [12], // replace with your contact segment IDs
        )
    );

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}

/**
 * Schedule a `draft` campaign to start sending at a future time (no more than 1 month ahead).
 * Accepts an ISO 8601 string or a \DateTimeInterface. The scheduled time is reported back in
 * `current_state_metadata.scheduled_at`.
 *
 * POST https://mailtrap.io/api/email_campaigns/{email_campaign_id}/schedule
 */
try {
    $response = $emailCampaigns->scheduleEmailCampaign(
        $emailCampaignId,
        new DateTimeImmutable('+1 week', new DateTimeZone('UTC'))
    );

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}

/**
 * Reset a `scheduled` campaign back to the `draft` state.
 *
 * POST https://mailtrap.io/api/email_campaigns/{email_campaign_id}/reset
 */
try {
    $response = $emailCampaigns->resetEmailCampaign($emailCampaignId);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}

/**
 * Schedule the campaign again so the cancellation below acts on a `scheduled` campaign.
 *
 * POST https://mailtrap.io/api/email_campaigns/{email_campaign_id}/schedule
 */
try {
    $response = $emailCampaigns->scheduleEmailCampaign(
        $emailCampaignId,
        new DateTimeImmutable('+1 day', new DateTimeZone('UTC'))
    );

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}

/**
 * Cancel a `scheduled` campaign, returning it to the `draft` state.
 *
 * POST https://mailtrap.io/api/email_campaigns/{email_campaign_id}/cancel
 */
try {
    $response = $emailCampaigns->cancelEmailCampaign($emailCampaignId);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}

/**
 * Start sending a `draft` campaign immediately.
 *
 * POST https://mailtrap.io/api/email_campaigns/{email_campaign_id}/start
 */
try {
    $response = $emailCampaigns->startEmailCampaign($emailCampaignId);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}

/**
 * Terminate a campaign that is currently sending (`started`, `queued`, or `paused`).
 *
 * POST https://mailtrap.io/api/email_campaigns/{email_campaign_id}/terminate
 */
try {
    $response = $emailCampaigns->terminateEmailCampaign($emailCampaignId);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}

/**
 * Get aggregated performance statistics for a campaign (wrapped in `data`).
 * All counts and rates are `0` when the campaign has never been started. Use the optional
 * `YYYY-MM-DD` date parameters to narrow the aggregation window.
 *
 * GET https://mailtrap.io/api/email_campaigns/{email_campaign_id}/stats
 */
try {
    $response = $emailCampaigns->getEmailCampaignStats(
        $emailCampaignId,
        startDate: (new DateTimeImmutable('-30 days'))->format('Y-m-d'),
        endDate: (new DateTimeImmutable('today'))->format('Y-m-d')
    );

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}

/**
 * Delete an email campaign. The campaign must not be in a sending state.
 * Returns `204 No Content` with an empty body.
 *
 * DELETE https://mailtrap.io/api/email_campaigns/{email_campaign_id}
 */
try {
    $response = $emailCampaigns->deleteEmailCampaign($emailCampaignId);

    var_dump($response->getStatusCode()); // 204
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}
