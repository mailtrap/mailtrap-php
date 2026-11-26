<?php

declare(strict_types=1);

namespace Mailtrap\Tests;

use Mailtrap\Api\Sending\Emails as TransactionSendingEmails;
use Mailtrap\Api\Sending\SendingInterface;
use Mailtrap\MailtrapClient;
use Mailtrap\MailtrapSendingClient;

/**
 * @covers MailtrapSendingClient
 *
 * Class MailtrapSendingClientTest
 */
class MailtrapSendingClientTest extends MailtrapClientTestCase
{
    private const DOMAIN_ID = 12345;

    public function getMailtrapClientClassName(): string
    {
        return MailtrapSendingClient::class;
    }

    public function getLayerInterfaceClassName(): string
    {
        return SendingInterface::class;
    }

    public static function mapInstancesProvider(): iterable
    {
        foreach (MailtrapSendingClient::API_MAPPING as $key => $item) {
            yield match ($key) {
                'suppressions', 'domains', 'stats', 'emailLogs', 'webhooks' => [$item, [self::FAKE_ACCOUNT_ID]],
                'companyInfo' => [$item, [self::DOMAIN_ID]],
                default => [$item],
            };
        }
    }

    public function testValidInitTransactionSendingEmails(): void
    {
        $this->assertInstanceOf(
            TransactionSendingEmails::class,
            MailtrapClient::initSendingEmails(apiKey: self::DEFAULT_API_KEY)
        );
    }
}
