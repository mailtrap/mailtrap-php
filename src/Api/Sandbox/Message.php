<?php

declare(strict_types=1);

namespace Mailtrap\Api\Sandbox;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Mailtrap\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Message
 */
class Message extends AbstractApi implements SandboxInterface
{
    public function __construct(
        ConfigInterface $config,
        private int $accountId,
        private int $inboxId,
    ) {
        parent::__construct($config);
    }

    /**
     * Get all messages in the inbox
     * Note: if you want to get all messages you need to use "page" param (by default will return only 30 messages per
     * page)
     *
     * @param int|null    $page          page of emails (per page = 30 messages, does not work with last_id param)
     * @param string|null $search        filter emails by this key; it works like case insensitive
     *                                   pattern matching by subject, to_email, to_name, namely if any of these fields
     *                                   at least start with the given search query, the response will return a result
     * @param int|null    $lastMessageId get emails, where primary key is less then this param (does not work with page
     *                                   param)
     *
     * @return ResponseInterface
     */
    public function getList(
        ?int $page = null,
        ?string $search = null,
        ?int $lastMessageId = null
    ): ResponseInterface {
        $parameters = [];

        if ($page !== null) {
            $parameters['page'] = $page;
        }

        if ($search !== null) {
            $parameters['search'] = $search;
        }

        if ($lastMessageId !== null) {
            $parameters['last_id'] = $lastMessageId;
        }

        return $this->handleResponse($this->httpGet(
            sprintf('%s/api/accounts/%s/inboxes/%s/messages', $this->getHost(), $this->getAccountId(), $this->getInboxId()),
            $parameters
        ));
    }

    /**
     * Get email message by ID.
     *
     * @param int $messageId
     *
     * @return ResponseInterface
     */
    public function getById(int $messageId): ResponseInterface
    {
        return $this->handleResponse($this->httpGet(
            sprintf('%s/api/accounts/%s/inboxes/%s/messages/%s', $this->getHost(), $this->getAccountId(), $this->getInboxId(), $messageId)
        ));
    }

    /**
     * Get a brief spam report by message ID.
     *
     * @param int $messageId
     *
     * @return ResponseInterface
     */
    public function getSpamScore(int $messageId): ResponseInterface
    {
        return $this->handleResponse($this->httpGet(sprintf(
            '%s/api/accounts/%s/inboxes/%s/messages/%s/spam_report',
            $this->getHost(),
            $this->getAccountId(),
            $this->getInboxId(),
            $messageId
        )));
    }

    /**
     * Get a brief HTML report by message ID.
     *
     * @param int $messageId
     *
     * @return ResponseInterface
     */
    public function getHtmlAnalysis(int $messageId): ResponseInterface
    {
        return $this->handleResponse($this->httpGet(sprintf(
            '%s/api/accounts/%s/inboxes/%s/messages/%s/analyze',
            $this->getHost(),
            $this->getAccountId(),
            $this->getInboxId(),
            $messageId
        )));
    }

    /**
     * Get the email headers parsed from the raw message.
     *
     * @param int $messageId
     *
     * @return ResponseInterface
     */
    public function getMailHeaders(int $messageId): ResponseInterface
    {
        return $this->handleResponse($this->httpGet(sprintf(
            '%s/api/accounts/%s/inboxes/%s/messages/%s/mail_headers',
            $this->getHost(),
            $this->getAccountId(),
            $this->getInboxId(),
            $messageId
        )));
    }

    /**
     * Get text email body, if it exists.
     *
     * @param int $messageId
     *
     * @return ResponseInterface
     */
    public function getText(int $messageId): ResponseInterface
    {
        return $this->handleResponse($this->httpGet(sprintf(
            '%s/api/accounts/%s/inboxes/%s/messages/%s/body.txt',
            $this->getHost(),
            $this->getAccountId(),
            $this->getInboxId(),
            $messageId
        )));
    }

    /**
     * Get raw email body.
     *
     * @param int $messageId
     *
     * @return ResponseInterface
     */
    public function getRaw(int $messageId): ResponseInterface
    {
        return $this->handleResponse($this->httpGet(sprintf(
            '%s/api/accounts/%s/inboxes/%s/messages/%s/body.raw',
            $this->getHost(),
            $this->getAccountId(),
            $this->getInboxId(),
            $messageId
        )));
    }

    /**
     * Get formatted HTML email body. Not applicable for plain text emails.
     *
     * @param int $messageId
     *
     * @return ResponseInterface
     */
    public function getHtml(int $messageId): ResponseInterface
    {
        return $this->handleResponse($this->httpGet(sprintf(
            '%s/api/accounts/%s/inboxes/%s/messages/%s/body.html',
            $this->getHost(),
            $this->getAccountId(),
            $this->getInboxId(),
            $messageId
        )));
    }

    /**
     * Get email message in .eml format.
     *
     * @param int $messageId
     *
     * @return ResponseInterface
     */
    public function getEml( int $messageId): ResponseInterface
    {
        return $this->handleResponse($this->httpGet(sprintf(
            '%s/api/accounts/%s/inboxes/%s/messages/%s/body.eml',
            $this->getHost(),
            $this->getAccountId(),
            $this->getInboxId(),
            $messageId
        )));
    }

    /**
     * Get HTML source of email.
     *
     * @param int $messageId
     *
     * @return ResponseInterface
     */
    public function getSource(int $messageId): ResponseInterface
    {
        return $this->handleResponse($this->httpGet(sprintf(
            '%s/api/accounts/%s/inboxes/%s/messages/%s/body.htmlsource',
            $this->getHost(),
            $this->getAccountId(),
            $this->getInboxId(),
            $messageId
        )));
    }

    /**
     * Update message attributes (right now only the is_read attribute is available for modification).
     *
     * @param int  $messageId
     * @param bool $isRead
     *
     * @return ResponseInterface
     */
    public function markAsRead(int $messageId, bool $isRead = true): ResponseInterface
    {
        return $this->handleResponse($this->httpPatch(
            sprintf('%s/api/accounts/%s/inboxes/%s/messages/%s', $this->getHost(), $this->getAccountId(), $this->getInboxId(), $messageId),
            [],
            [
                'message' => [
                    'is_read' => $isRead
                ]
            ]
        ));
    }

    /**
     * Delete message from inbox.
     *
     * @param int $messageId
     *
     * @return ResponseInterface
     */
    public function delete(int $messageId): ResponseInterface
    {
        return $this->handleResponse($this->httpDelete(
            sprintf('%s/api/accounts/%s/inboxes/%s/messages/%s', $this->getHost(), $this->getAccountId(), $this->getInboxId(), $messageId)
        ));
    }

    /**
     * Forward a message to a recipient email.
     *
     * @param int    $messageId
     * @param string $email     recipient email address
     *
     * @return ResponseInterface
     */
    public function forward(int $messageId, string $email): ResponseInterface
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException(sprintf('Invalid recipient email: "%s"', $email));
        }

        return $this->handleResponse($this->httpPost(
            sprintf('%s/api/accounts/%s/inboxes/%s/messages/%s/forward', $this->getHost(), $this->getAccountId(), $this->getInboxId(), $messageId),
            [],
            ['email' => $email]
        ));
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function getInboxId(): int
    {
        return $this->inboxId;
    }
}
