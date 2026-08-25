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

    public function mapInstancesProvider(): iterable
    {
        foreach (MailtrapSendingClient::API_MAPPING as $key => $item) {
            yield match ($key) {
                'suppressions', 'domains', 'stats', 'emailLogs', 'webhooks' => [new $item($this->getConfigMock(), self::FAKE_ACCOUNT_ID)],
                'companyInfo' => [new $item($this->getConfigMock(), self::DOMAIN_ID)],
                default => [new $item($this->getConfigMock())],
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
