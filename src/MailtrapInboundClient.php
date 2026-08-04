<?php

declare(strict_types=1);

namespace Mailtrap;

/**
 * @method Api\Inbound\Folder  folders()
 * @method Api\Inbound\Inbox   inboxes(int $folderId)
 * @method Api\Inbound\Message messages(int $inboxId)
 * @method Api\Inbound\Thread  threads(int $inboxId)
 *
 * Class MailtrapInboundClient
 */
final class MailtrapInboundClient extends AbstractMailtrapClient
{
    public const API_MAPPING = [
        'folders' => Api\Inbound\Folder::class,
        'inboxes' => Api\Inbound\Inbox::class,
        'messages' => Api\Inbound\Message::class,
        'threads' => Api\Inbound\Thread::class,
    ];
}
