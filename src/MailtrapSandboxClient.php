<?php

declare(strict_types=1);

namespace Mailtrap;

/**
 * @method  Api\Sandbox\Emails      emails(int $sandboxId)
 * @method  Api\Sandbox\Project     projects(int $accountId)
 * @method  Api\Sandbox\Sandbox     sandboxes(int $accountId)
 * @method  Api\Sandbox\Attachment  attachments(int $accountId, int $sandboxId)
 * @method  Api\Sandbox\Message     messages(int $accountId, int $sandboxId)
 * @method  Api\Sandbox\Inbox       inboxes(int $accountId) Deprecated: use sandboxes() instead.
 *
 * Class MailtrapSandboxClient
 */
final class MailtrapSandboxClient extends AbstractMailtrapClient implements EmailsSendMailtrapClientInterface
{
    public const API_MAPPING = [
        'emails' => Api\Sandbox\Emails::class,
        'projects' => Api\Sandbox\Project::class,
        'sandboxes' => Api\Sandbox\Sandbox::class,
        'inboxes' => Api\Sandbox\Inbox::class,
        'attachments' => Api\Sandbox\Attachment::class,
        'messages' => Api\Sandbox\Message::class,
    ];
}
