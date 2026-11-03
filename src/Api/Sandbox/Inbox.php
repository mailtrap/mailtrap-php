<?php

declare(strict_types=1);

namespace Mailtrap\Api\Sandbox;

use Mailtrap\ConfigInterface;
use Mailtrap\DTO\Request\Sandbox as SandboxRequest;
use Mailtrap\Helper\Deprecation;
use Psr\Http\Message\ResponseInterface;

/**
 * The overrides below only keep the old parameter names, so calls with named arguments keep working.
 *
 * @deprecated Use {@see Sandbox} (MailtrapSandboxClient::sandboxes()) instead.
 * @psalm-suppress ParamNameMismatch
 *
 * Class Inbox
 */
class Inbox extends Sandbox
{
    public function __construct(ConfigInterface $config, int $accountId)
    {
        Deprecation::trigger(
            self::class . ' (MailtrapSandboxClient::inboxes())',
            Sandbox::class . ' (MailtrapSandboxClient::sandboxes())'
        );

        parent::__construct($config, $accountId);
    }

    /**
     * @deprecated Use {@see Sandbox::getSandboxAttributes()} instead.
     */
    public function getInboxAttributes(int $inboxId): ResponseInterface
    {
        Deprecation::trigger(self::class . '::getInboxAttributes()', Sandbox::class . '::getSandboxAttributes()');

        return $this->getSandboxAttributes($inboxId);
    }

    public function create(int $projectId, string $inboxName): ResponseInterface
    {
        return parent::create($projectId, $inboxName);
    }

    public function delete(int $inboxId): ResponseInterface
    {
        return parent::delete($inboxId);
    }

    public function update(int $inboxId, SandboxRequest $updateInbox): ResponseInterface
    {
        return parent::update($inboxId, $updateInbox);
    }

    public function clean(int $inboxId): ResponseInterface
    {
        return parent::clean($inboxId);
    }

    public function markAsRead(int $inboxId): ResponseInterface
    {
        return parent::markAsRead($inboxId);
    }

    public function resetSmtpCredentials(int $inboxId): ResponseInterface
    {
        return parent::resetSmtpCredentials($inboxId);
    }

    public function toggleEmailAddress(int $inboxId): ResponseInterface
    {
        return parent::toggleEmailAddress($inboxId);
    }

    public function resetEmailAddress(int $inboxId): ResponseInterface
    {
        return parent::resetEmailAddress($inboxId);
    }
}
