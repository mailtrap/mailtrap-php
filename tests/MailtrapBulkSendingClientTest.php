<?php

declare(strict_types=1);

namespace Mailtrap\Tests;

use Mailtrap\Api\BulkSending\BulkSendingInterface;
use Mailtrap\Api\BulkSending\Emails as BulkSendingEmails;
use Mailtrap\Exception\InvalidArgumentException;
use Mailtrap\MailtrapBulkSendingClient;
use Mailtrap\MailtrapClient;

/**
 * @covers MailtrapBulkSendingClientTest
 *
 * Class MailtrapBulkSendingClientTest
 */
class MailtrapBulkSendingClientTest extends MailtrapClientTestCase
{
    public function getMailtrapClientClassName(): string
    {
        return MailtrapBulkSendingClient::class;
    }

    public function getLayerInterfaceClassName(): string
    {
        return BulkSendingInterface::class;
    }

    public static function mapInstancesProvider(): iterable
    {
        foreach (MailtrapBulkSendingClient::API_MAPPING as $item) {
            yield [$item];
        }
    }

    public function testValidInitBulkSendingEmails(): void
    {
        $this->assertInstanceOf(
            BulkSendingEmails::class,
            MailtrapClient::initSendingEmails(apiKey: self::DEFAULT_API_KEY, isBulk: true)
        );
    }

    public function testInValidInitBulkSendingEmails(): void
    {
        $this->expectException(InvalidArgumentException::class);

        MailtrapClient::initSendingEmails(apiKey: self::DEFAULT_API_KEY, isBulk: true, isSandbox: true);
    }
}
