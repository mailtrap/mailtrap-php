<?php

declare(strict_types=1);

namespace Mailtrap\Tests;

use Mailtrap\Api\Sandbox\Emails as SandboxSendingEmails;
use Mailtrap\Api\Sandbox\Inbox;
use Mailtrap\Api\Sandbox\Sandbox;
use Mailtrap\Api\Sandbox\SandboxInterface;
use Mailtrap\Exception\InvalidArgumentException;
use Mailtrap\MailtrapClient;
use Mailtrap\MailtrapSandboxClient;

/**
 * @covers MailtrapSandboxClient
 *
 * Class MailtrapSandboxClientTest
 */
class MailtrapSandboxClientTest extends MailtrapClientTestCase
{
    use AssertsDeprecationsTrait;

    public function getMailtrapClientClassName(): string
    {
        return MailtrapSandboxClient::class;
    }

    public function getLayerInterfaceClassName(): string
    {
        return SandboxInterface::class;
    }

    public function mapInstancesProvider(): iterable
    {
        foreach (MailtrapSandboxClient::API_MAPPING as $key => $item) {
            yield match ($key) {
                'emails' => [new $item($this->getConfigMock(), self::FAKE_SANDBOX_ID)],
                'projects', 'sandboxes', 'inboxes' => [new $item($this->getConfigMock(), self::FAKE_ACCOUNT_ID)],
                'messages', 'attachments' => [new $item($this->getConfigMock(), self::FAKE_ACCOUNT_ID, self::FAKE_SANDBOX_ID)],
                default => [new $item($this->getConfigMock())],
            };
        }
    }

    public function testSandboxes(): void
    {
        $sandboxes = $this->assertNoDeprecationTriggered(fn () => $this->mailtrapClientLayer->sandboxes(self::FAKE_ACCOUNT_ID));

        $this->assertInstanceOf(Sandbox::class, $sandboxes);
        $this->assertNotInstanceOf(Inbox::class, $sandboxes);
        $this->assertSame(self::FAKE_ACCOUNT_ID, $sandboxes->getAccountId());
    }

    public function testInboxesIsDeprecated(): void
    {
        $inboxes = $this->assertDeprecationTriggered(
            'Mailtrap\Api\Sandbox\Inbox (MailtrapSandboxClient::inboxes()) is deprecated and will be removed in a future major release; use Mailtrap\Api\Sandbox\Sandbox (MailtrapSandboxClient::sandboxes()) instead.',
            fn () => $this->mailtrapClientLayer->inboxes(self::FAKE_ACCOUNT_ID)
        );

        $this->assertInstanceOf(Inbox::class, $inboxes);
        $this->assertInstanceOf(Sandbox::class, $inboxes);
        $this->assertSame(self::FAKE_ACCOUNT_ID, $inboxes->getAccountId());
    }

    /**
     * @dataProvider sandboxIdApisProvider
     */
    public function testSandboxIdArgument(string $method, array $arguments): void
    {
        $api = $this->assertNoDeprecationTriggered(
            fn () => $this->mailtrapClientLayer->{$method}(...$arguments, ...['sandboxId' => self::FAKE_SANDBOX_ID])
        );

        $this->assertSame(self::FAKE_SANDBOX_ID, $api->getSandboxId());
    }

    /**
     * @dataProvider sandboxIdApisProvider
     */
    public function testInboxIdArgumentIsDeprecated(string $method, array $arguments, string $className): void
    {
        $api = $this->assertDeprecationTriggered(
            sprintf('The $inboxId argument of %s::__construct() is deprecated', $className),
            fn () => $this->mailtrapClientLayer->{$method}(...$arguments, ...['inboxId' => self::FAKE_SANDBOX_ID])
        );

        $this->assertSame(self::FAKE_SANDBOX_ID, $api->getSandboxId());
    }

    /**
     * @dataProvider sandboxIdApisProvider
     */
    public function testGetInboxIdIsDeprecated(string $method, array $arguments, string $className): void
    {
        $api = $this->mailtrapClientLayer->{$method}(...$arguments, ...[self::FAKE_SANDBOX_ID]);

        $inboxId = $this->assertDeprecationTriggered(
            sprintf('%1$s::getInboxId() is deprecated and will be removed in a future major release; use %1$s::getSandboxId() instead.', $className),
            fn () => $api->getInboxId()
        );

        $this->assertSame(self::FAKE_SANDBOX_ID, $inboxId);
    }

    /**
     * @dataProvider sandboxIdApisProvider
     */
    public function testSandboxIdIsRequired(string $method, array $arguments, string $className): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException(sprintf('%s::__construct() requires the $sandboxId argument.', $className))
        );

        $this->mailtrapClientLayer->{$method}(...$arguments);
    }

    /**
     * @dataProvider sandboxIdApisProvider
     */
    public function testDifferentSandboxIdAndInboxIdAreRejected(string $method, array $arguments, string $className): void
    {
        $this->expectExceptionObject(new InvalidArgumentException(sprintf(
            '%s::__construct() received different values for $sandboxId and $inboxId; pass only $sandboxId.',
            $className
        )));

        $this->mailtrapClientLayer->{$method}(...$arguments, ...['sandboxId' => self::FAKE_SANDBOX_ID, 'inboxId' => self::FAKE_SANDBOX_ID + 1]);
    }

    public function sandboxIdApisProvider(): iterable
    {
        yield ['emails', [], SandboxSendingEmails::class];
        yield ['messages', [self::FAKE_ACCOUNT_ID], MailtrapSandboxClient::API_MAPPING['messages']];
        yield ['attachments', [self::FAKE_ACCOUNT_ID], MailtrapSandboxClient::API_MAPPING['attachments']];
    }

    public function testValidInitSandboxSendingEmails(): void
    {
        $emails = $this->assertNoDeprecationTriggered(
            fn () => MailtrapClient::initSendingEmails(apiKey: self::DEFAULT_API_KEY, isSandbox: true, sandboxId: self::FAKE_SANDBOX_ID)
        );

        $this->assertInstanceOf(SandboxSendingEmails::class, $emails);
        $this->assertSame(self::FAKE_SANDBOX_ID, $emails->getSandboxId());
    }

    public function testInitSandboxSendingEmailsWithInboxIdIsDeprecated(): void
    {
        $emails = $this->assertDeprecationTriggered(
            'The $inboxId argument of Mailtrap\MailtrapClient::initSendingEmails() is deprecated and will be removed in a future major release; use $sandboxId instead.',
            fn () => MailtrapClient::initSendingEmails(apiKey: self::DEFAULT_API_KEY, isSandbox: true, inboxId: self::FAKE_SANDBOX_ID)
        );

        $this->assertInstanceOf(SandboxSendingEmails::class, $emails);
        $this->assertSame(self::FAKE_SANDBOX_ID, $emails->getSandboxId());
    }
}
