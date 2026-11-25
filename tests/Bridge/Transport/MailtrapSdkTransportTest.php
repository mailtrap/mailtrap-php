<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Bridge\Transport;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Sending\Emails;
use Mailtrap\Bridge\Transport\MailtrapSdkTransport;
use Mailtrap\Config;
use Mailtrap\MailtrapBulkSendingClient;
use Mailtrap\MailtrapSandboxClient;
use Mailtrap\MailtrapSendingClient;
use Mailtrap\Mime\MailtrapEmail;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\RuntimeException;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\Header\TagHeader;
use Symfony\Component\Mime\Email;

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

    public function testSendConvertsTagAndMetadataHeaders(): void
    {
        $email = $this->createEmail(new Email());
        $email->getHeaders()
            ->add(new TagHeader('welcome'))
            ->add(new MetadataHeader('user_id', '42'))
            ->add(new MetadataHeader('plan', 'pro'))
            ->addTextHeader('X-Message-Source', 'dev.mydomain.com');

        $emails = $this->getEmailsMock();
        $emails
            ->expects($this->once())
            ->method('httpPost')
            ->with(AbstractApi::SENDMAIL_TRANSACTIONAL_HOST . '/api/send', [], [
                'from' => ['email' => 'foo@example.com'],
                'to' => [['email' => 'bar@example.com']],
                'subject' => 'Welcome',
                'text' => 'Some text',
                // no X-Tag and no X-Metadata-* here
                'headers' => ['X-Message-Source' => 'dev.mydomain.com'],
                'category' => 'welcome',
                'custom_variables' => ['user_id' => '42', 'plan' => 'pro'],
            ])
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'message_ids' => ['0c7fd939-02cf-11ed-88c2-0a58a9feac02'],
            ])));

        $sentMessage = $this->getTransport($emails)->send($email);

        $this->assertSame('0c7fd939-02cf-11ed-88c2-0a58a9feac02', $sentMessage->getMessageId());
        // neither the caller's message nor the one event listeners and the profiler hold is changed
        foreach ([$email, $sentMessage->getOriginalMessage()] as $message) {
            $this->assertTrue($message->getHeaders()->has('X-Tag'));
            $this->assertFalse($message->getHeaders()->has('category'));
        }
    }

    public function testSendFailsOnMoreThanOneTag(): void
    {
        $email = $this->createEmail(new Email());
        $email->getHeaders()->add(new TagHeader('first'))->add(new TagHeader('second'));

        $emails = $this->getEmailsMock();
        $emails->expects($this->never())->method('httpPost');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Mailtrap does not accept more than 1 category in the email.');

        $this->getTransport($emails)->send($email);
    }

    public function testSendFailsOnTagNextToCategory(): void
    {
        $email = $this->createEmail((new MailtrapEmail())->category('mine'));
        $email->getHeaders()->add(new TagHeader('welcome'));

        $emails = $this->getEmailsMock();
        $emails->expects($this->never())->method('httpPost');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Mailtrap does not accept more than 1 category in the email.');

        $this->getTransport($emails)->send($email);
    }

    private function createEmail(Email $email): Email
    {
        return $email
            ->from('foo@example.com')
            ->to('bar@example.com')
            ->subject('Welcome')
            ->text('Some text')
        ;
    }

    private function getEmailsMock(): Emails
    {
        return $this->getMockBuilder(Emails::class)
            ->onlyMethods(['httpPost'])
            ->setConstructorArgs([$this->getSendConfig()])
            ->getMock()
        ;
    }

    private function getTransport(Emails $emails): MailtrapSdkTransport
    {
        return new MailtrapSdkTransport($emails, $this->getSendConfig());
    }

    private function getSendConfig(): Config
    {
        return (new Config(self::DEFAULT_API_KEY))->setHost(AbstractApi::SENDMAIL_TRANSACTIONAL_HOST);
    }
}
