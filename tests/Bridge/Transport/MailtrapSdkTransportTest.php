<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Bridge\Transport;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Bridge\Transport\MailtrapSdkTransport;
use Mailtrap\Config;
use Mailtrap\MailtrapBulkSendingClient;
use Mailtrap\MailtrapSandboxClient;
use Mailtrap\MailtrapSendingClient;
use Mailtrap\Tests\MailtrapTestCase;
use Symfony\Component\Mailer\Envelope;

/**
 * @covers MailtrapSdkTransport
 */
class MailtrapSdkTransportTest extends MailtrapTestCase
{
    protected function setUp(): void
    {
        if (!class_exists(Envelope::class)) {
            $this->markTestSkipped(
                'The "MailtrapSdkTransportTest" tests skipped, because "symfony/mailer" package is not installed.'
            );
        }

        parent::setUp();
    }

    /**
     * @dataProvider getTransportData
     */
    public function testToString(MailtrapSdkTransport $transport, string $expected): void
    {
        $this->assertSame($expected, (string) $transport);
    }

    public static function getTransportData(): array
    {
        $sendConfig = (new Config('key'))->setHost(AbstractApi::SENDMAIL_TRANSACTIONAL_HOST);
        $sandboxConfig = (new Config('key'))->setHost(AbstractApi::SENDMAIL_SANDBOX_HOST);
        $bulkConfig = (new Config('key'))->setHost(AbstractApi::SENDMAIL_BULK_HOST);
        $sandboxId = 1234;

        return [
            [
                new MailtrapSdkTransport(
                    (new MailtrapSendingClient($sendConfig))->emails(),
                    $sendConfig
                ),
                sprintf('mailtrap+sdk://%s', AbstractApi::SENDMAIL_TRANSACTIONAL_HOST),
            ],
            [
                new MailtrapSdkTransport(
                    (new MailtrapSandboxClient($sandboxConfig))->emails($sandboxId),
                    $sandboxConfig
                ),
                sprintf('mailtrap+sdk://%s?sandboxId=%s', AbstractApi::SENDMAIL_SANDBOX_HOST, $sandboxId),
            ],
            [
                new MailtrapSdkTransport(
                    (new MailtrapBulkSendingClient($bulkConfig))->emails(),
                    $bulkConfig
                ),
                sprintf('mailtrap+sdk://%s', AbstractApi::SENDMAIL_BULK_HOST),
            ],
        ];
    }
}
