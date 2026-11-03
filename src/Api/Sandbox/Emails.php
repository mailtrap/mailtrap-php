<?php

declare(strict_types=1);

namespace Mailtrap\Api\Sandbox;

use Mailtrap\Api\AbstractEmails;
use Mailtrap\ConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Mime\Email;

/**
 * Class Emails
 */
class Emails extends AbstractEmails implements SandboxInterface
{
    use SandboxIdTrait;

    /**
     * @param int|null $inboxId deprecated, use $sandboxId instead
     */
    public function __construct(ConfigInterface $config, ?int $sandboxId = null, ?int $inboxId = null)
    {
        parent::__construct($config);
        $this->setSandboxId($sandboxId, $inboxId);
    }

    public function send(Email $email): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost(sprintf('%s/api/send/%s', $this->getHost(), $this->getSandboxId()), [], $this->getPayload($email))
        );
    }

    public function batchSend(array $recipientEmails, ?Email $baseEmail = null): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost(
                sprintf('%s/api/batch/%s', $this->getHost(), $this->getSandboxId()),
                [],
                $this->getBatchBody($recipientEmails, $baseEmail),
            )
        );
    }

    protected function getHost(): string
    {
        return $this->config->getHost() ?: self::SENDMAIL_SANDBOX_HOST;
    }
}
