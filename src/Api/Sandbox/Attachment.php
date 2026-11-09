<?php

declare(strict_types=1);

namespace Mailtrap\Api\Sandbox;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Attachment
 */
class Attachment extends AbstractApi implements SandboxInterface
{
    use SandboxIdTrait;

    /**
     * @param int|null $inboxId deprecated, use $sandboxId instead
     */
    public function __construct(
        ConfigInterface $config,
        private int $accountId,
        ?int $sandboxId = null,
        ?int $inboxId = null,
    ) {
        parent::__construct($config);
        $this->setSandboxId($sandboxId, $inboxId);
    }

    /**
     * Get message attachments by sandboxId and messageId.
     *
     * @param int         $messageId
     * @param string|null $attachmentType
     *
     * @return ResponseInterface
     */
    public function getMessageAttachments(
        int $messageId,
        ?string $attachmentType = null
    ): ResponseInterface {
        $parameters = [];
        if (!empty($attachmentType)) {
            $parameters = [
                'attachment_type' => $attachmentType
            ];
        }

        return $this->handleResponse($this->httpGet(
            sprintf(
                '%s/api/accounts/%s/inboxes/%s/messages/%s/attachments',
                $this->getHost(),
                $this->getAccountId(),
                $this->getSandboxId(),
                $messageId
            ),
            $parameters
        ));
    }

    /**
     * Get message single attachment by id.
     *
     * @param int $messageId
     * @param int $attachmentId
     *
     * @return ResponseInterface
     */
    public function getMessageAttachment(int $messageId, int $attachmentId): ResponseInterface
    {
        return $this->handleResponse($this->httpGet(sprintf(
            '%s/api/accounts/%s/inboxes/%s/messages/%s/attachments/%s',
            $this->getHost(),
            $this->getAccountId(),
            $this->getSandboxId(),
            $messageId,
            $attachmentId
        )));
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }
}
