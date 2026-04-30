<?php

use Mailtrap\Config;
use Mailtrap\DTO\Request\Webhook\CreateWebhook;
use Mailtrap\DTO\Request\Webhook\UpdateWebhook;
use Mailtrap\DTO\Request\Webhook\WebhookInterface;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapSendingClient;

require __DIR__ . '/../../vendor/autoload.php';

$accountId = $_ENV['MAILTRAP_ACCOUNT_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens
$webhooks = (new MailtrapSendingClient($config))->webhooks($accountId);

/**
 * List all webhooks in the account.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/webhooks
 */
try {
    $response = $webhooks->getWebhooks();

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

/**
 * Get a single webhook by ID.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/webhooks/{webhook_id}
 */
try {
    $webhookId = 1;

    $response = $webhooks->getWebhook($webhookId);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

/**
 * Create a new webhook. The response includes a signing_secret used to verify webhook signatures.
 *
 * POST https://mailtrap.io/api/accounts/{account_id}/webhooks
 */
try {
    $response = $webhooks->createWebhook(new CreateWebhook(
        url: 'https://example.com/mailtrap/webhooks',
        webhookType: WebhookInterface::TYPE_EMAIL_SENDING,
        eventTypes: [
            WebhookInterface::EVENT_DELIVERY,
            WebhookInterface::EVENT_BOUNCE,
            WebhookInterface::EVENT_OPEN,
        ],
        payloadFormat: WebhookInterface::PAYLOAD_FORMAT_JSON,
        sendingStream: WebhookInterface::SENDING_STREAM_TRANSACTIONAL,
        domainId: $_ENV['MAILTRAP_DOMAIN_ID'] ?? null,
    ));

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

/**
 * Update an existing webhook.
 * Only `url`, `active`, `payload_format`, and `event_types` are mutable.
 *
 * PATCH https://mailtrap.io/api/accounts/{account_id}/webhooks/{webhook_id}
 */
try {
    $webhookId = 1;

    $response = $webhooks->updateWebhook(
        $webhookId,
        new UpdateWebhook(
            url: 'https://example.com/new-endpoint',
            active: false,
        )
    );

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

/**
 * Delete a webhook by ID.
 *
 * DELETE https://mailtrap.io/api/accounts/{account_id}/webhooks/{webhook_id}
 */
try {
    $webhookId = 1;

    $response = $webhooks->deleteWebhook($webhookId);

    var_dump($response->getStatusCode());
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
