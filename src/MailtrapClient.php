<?php

declare(strict_types=1);

namespace Mailtrap;

use Mailtrap\Api\EmailsSendApiInterface;
use Mailtrap\Exception\InvalidArgumentException;

/**
 * The main entry point to use all possible API layers
 *
 * @method  MailtrapGeneralClient       general
 * @method  MailtrapSandboxClient       sandbox
 * @method  MailtrapSendingClient       sending
 * @method  MailtrapBulkSendingClient   bulkSending
 * @method  MailtrapInboundClient       inbound
 *
 * Class MailtrapClient
 */
class MailtrapClient extends AbstractMailtrapClient
{
    public const LAYER_GENERAL = 'general';
    public const LAYER_SANDBOX = 'sandbox';
    public const LAYER_TRANSACTIONAL_SENDING = 'sending';
    public const LAYER_BULK_SENDING = 'bulkSending';
    public const LAYER_INBOUND = 'inbound';

    public const API_MAPPING = [
        self::LAYER_GENERAL => MailtrapGeneralClient::class,
        self::LAYER_SANDBOX => MailtrapSandboxClient::class,
        self::LAYER_TRANSACTIONAL_SENDING => MailtrapSendingClient::class,
        self::LAYER_BULK_SENDING => MailtrapBulkSendingClient::class,
        self::LAYER_INBOUND => MailtrapInboundClient::class,
    ];

    public static function initSendingEmails(
        string $apiKey,
        bool $isBulk = false,
        bool $isSandbox = false,
        ?int $inboxId = null,
    ): EmailsSendApiInterface {
        $client = new self(new Config($apiKey));

        if ($isBulk && $isSandbox) {
            throw new InvalidArgumentException('Bulk mode is not applicable for sandbox API');
        }

        if ($isSandbox) {
            return $client->sandbox()->emails($inboxId);
        }

        if ($isBulk) {
            return $client->bulkSending()->emails();
        }

        return $client->sending()->emails();
    }
}
