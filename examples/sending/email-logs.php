<?php

declare(strict_types=1);

use Mailtrap\Config;
use Mailtrap\DTO\Request\EmailLogs\EmailLogsListFilters;
use Mailtrap\DTO\Request\EmailLogs\FilterCriterion;
use Mailtrap\DTO\Request\EmailLogs\EmailLogsFilterOperator;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapSendingClient;

require __DIR__ . '/../../vendor/autoload.php';

$accountId = (int) $_ENV['MAILTRAP_ACCOUNT_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']);
$emailLogs = (new MailtrapSendingClient($config))->emailLogs($accountId);

/**
 * List email logs (paginated).
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/email_logs
 */
try {
    // Get first page (no filters)
    $response = $emailLogs->getList();
    $data = ResponseHelper::toArray($response);

    echo "Total count: " . $data['total_count'] . PHP_EOL;
    foreach ($data['messages'] as $msg) {
        echo "  - " . $msg['message_id'] . " | " . $msg['status'] . " | " . ($msg['subject'] ?? '') . PHP_EOL;
    }

    // Optional: filter by date range (last 2 days), recipient, categories – using the filters model
    $sentBefore = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DateTimeInterface::ATOM);
    $sentAfter = (new DateTimeImmutable('-2 days', new DateTimeZone('UTC')))->format(DateTimeInterface::ATOM);
    $filters = EmailLogsListFilters::create(
        $sentAfter,
        $sentBefore,
        [
            'subject' => FilterCriterion::withoutValue(EmailLogsFilterOperator::NOT_EMPTY),
            'to' => FilterCriterion::withValue(EmailLogsFilterOperator::CI_EQUAL, 'recipient@example.com'),
            'category' => FilterCriterion::withValue(EmailLogsFilterOperator::EQUAL, ['Welcome Email', 'Order Confirmation']),
        ]
    );
    $response = $emailLogs->getList($filters);
    $data = ResponseHelper::toArray($response);

    // Next page (use next_page_cursor from previous response)
    $cursor = $data['next_page_cursor'] ?? null;
    if ($cursor !== null) {
        $response = $emailLogs->getList($filters, $cursor);
        $data = ResponseHelper::toArray($response);
    }
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}

/**
 * Get a single email log message by ID.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/email_logs/{sending_message_id}
 */
try {
    $listResponse = $emailLogs->getList();
    $listData = ResponseHelper::toArray($listResponse);
    $messageId = isset($listData['messages'][0]) ? $listData['messages'][0]['message_id'] : null;

    if ($messageId !== null) {
        $response = $emailLogs->getMessage($messageId);
        $message = ResponseHelper::toArray($response);

        echo "Message: " . $message['message_id'] . PHP_EOL;
        echo "Status: " . $message['status'] . PHP_EOL;
        echo "Subject: " . ($message['subject'] ?? '') . PHP_EOL;
        echo "Events: " . count($message['events'] ?? []) . PHP_EOL;
    } else {
        echo "No messages in the list." . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}
