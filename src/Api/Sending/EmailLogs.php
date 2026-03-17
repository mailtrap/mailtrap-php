<?php

declare(strict_types=1);

namespace Mailtrap\Api\Sending;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Mailtrap\DTO\Request\EmailLogs\EmailLogsListFilters;
use Mailtrap\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

/**
 * Email Logs API.
 *
 * List and retrieve email sending logs for the account.
 * @see https://mailtrap.io/docs/api/sending/email-logs
 */
class EmailLogs extends AbstractApi implements SendingInterface
{
    public function __construct(ConfigInterface $config, private int $accountId)
    {
        parent::__construct($config);
    }

    /**
     * List email logs (paginated).
     *
     * Returns a paginated list of email logs for the account. Results are restricted to sending domains
     * the authenticated token has access to. Invalid or unknown filters are ignored.
     * Results are ordered by sent_at descending.
     *
     * @param array<string, mixed>|EmailLogsListFilters $filters Filter criteria: either an EmailLogsListFilters instance
     *                                                           or a raw array (deep object). Array keys: sent_after,
     *                                                           sent_before, to, from, subject, status, events,
     *                                                           clicks_count, opens_count, client_ip, sending_ip,
     *                                                           email_service_provider_response, email_service_provider,
     *                                                           recipient_mx, category, sending_domain_id, sending_stream.
     * @param string|null $searchAfter Cursor for the next page (message_id UUID from previous response next_page_cursor).
     * @return ResponseInterface 200 with body: { messages: array, total_count: int, next_page_cursor: string|null }
     */
    public function getList(array|EmailLogsListFilters $filters = [], ?string $searchAfter = null): ResponseInterface
    {
        $params = [];
        if ($searchAfter !== null && $searchAfter !== '') {
            $params['search_after'] = $searchAfter;
        }
        $filterArray = $filters instanceof EmailLogsListFilters ? $filters->toArray() : $filters;
        if ($filterArray !== []) {
            $params['filters'] = $filterArray;
        }

        return $this->handleResponse(
            $this->httpGet($this->getBasePath(), $params)
        );
    }

    /**
     * Get a single email log message by ID.
     *
     * Returns message details including events. Message must belong to the account and a sending domain
     * the token can access.
     *
     * @param string $sendingMessageId Message UUID (sending_message_id).
     * @return ResponseInterface 200 with SendingMessage body (message_id, status, subject, from, to, sent_at, events, etc.)
     */
    public function getMessage(string $sendingMessageId): ResponseInterface
    {
        if (trim($sendingMessageId) === '') {
            throw new InvalidArgumentException('sending_message_id must not be empty.');
        }

        return $this->handleResponse(
            $this->httpGet(
                sprintf('%s/%s', $this->getBasePath(), $sendingMessageId)
            )
        );
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/accounts/%s/email_logs', $this->getHost(), $this->accountId);
    }
}
