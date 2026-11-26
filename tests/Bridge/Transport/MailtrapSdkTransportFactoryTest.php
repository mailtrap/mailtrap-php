<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Bridge\Transport;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Bridge\Transport\MailtrapSdkTransport;
use Mailtrap\Bridge\Transport\MailtrapSdkTransportFactory;
use Mailtrap\Config;
use Mailtrap\Exception\InvalidArgumentException;
use Mailtrap\Exception\RuntimeException;
use Mailtrap\MailtrapClient;
use Mailtrap\Tests\AssertsDeprecationsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;

/**
 * @covers MailtrapSdkTransportFactory
 *
 * Class MailtrapSdkTransportFactoryTest
 */
class MailtrapSdkTransportFactoryTest extends TransportFactoryTestCase
{
    use AssertsDeprecationsTrait;

    protected function setUp(): void
    {
        if (!class_exists(Dsn::class)) {
            $this->markTestSkipped(
                'The "MailtrapSdkTransportFactoryTest" tests skipped, because "symfony/mailer" package is not installed.'
            );
        }

        parent::setUp();
    }

    public function getFactory(): TransportFactoryInterface
    {
        return new MailtrapSdkTransportFactory($this->getDispatcher(), $this->getClient(), $this->getLogger());
    }

    public static function supportsProvider(): iterable
    {
        yield [
            new Dsn('mailtrap+sdk', 'default'),
            true,
        ];
    }

    public static function createProvider(): iterable
    {
        $sandboxId = 1234;

        yield [
            new Dsn('mailtrap+sdk', 'default', self::USER),
            static fn (self $test) => $test->createTransport(
                AbstractApi::SENDMAIL_TRANSACTIONAL_HOST,
                static fn (MailtrapClient $client) => $client->sending()->emails()
            ),
        ];

        yield [
            new Dsn('mailtrap+sdk', AbstractApi::SENDMAIL_TRANSACTIONAL_HOST, self::USER),
            static fn (self $test) => $test->createTransport(
                AbstractApi::SENDMAIL_TRANSACTIONAL_HOST,
                static fn (MailtrapClient $client) => $client->sending()->emails()
            ),
        ];

        // sandbox
        yield [
            new Dsn('mailtrap+sdk', AbstractApi::SENDMAIL_SANDBOX_HOST, self::USER, null, null, ['sandboxId' => $sandboxId]),
            static fn (self $test) => $test->createTransport(
                AbstractApi::SENDMAIL_SANDBOX_HOST,
                static fn (MailtrapClient $client) => $client->sandbox()->emails($sandboxId)
            ),
        ];

        // bulk sending
        yield [
            new Dsn('mailtrap+sdk', AbstractApi::SENDMAIL_BULK_HOST, self::USER),
            static fn (self $test) => $test->createTransport(
                AbstractApi::SENDMAIL_BULK_HOST,
                static fn (MailtrapClient $client) => $client->bulkSending()->emails()
            ),
        ];
    }

    private function createTransport(string $host, \Closure $emailsApi): MailtrapSdkTransport
    {
        $config = (new Config(self::USER))
            ->setHttpClient(new Psr18Client($this->getClient()))
            ->setHost($host);

        return new MailtrapSdkTransport(
            $emailsApi(new MailtrapClient($config)),
            $config,
            $this->getDispatcher(),
            $this->getLogger()
        );
    }

    public function testSandboxIdOptionTriggersNoDeprecation(): void
    {
        $transport = $this->assertNoDeprecationTriggered(
            fn () => $this->getFactory()->create(
                new Dsn('mailtrap+sdk', AbstractApi::SENDMAIL_SANDBOX_HOST, self::USER, null, null, ['sandboxId' => '1234'])
            )
        );

        $this->assertSame(sprintf('mailtrap+sdk://%s?sandboxId=1234', AbstractApi::SENDMAIL_SANDBOX_HOST), (string) $transport);
    }

    /**
     * @dataProvider deprecatedInboxIdOptionsProvider
     */
    #[DataProvider('deprecatedInboxIdOptionsProvider')]
    public function testInboxIdOptionIsDeprecated(array $options): void
    {
        $transport = $this->assertDeprecationTriggered(
            'The "inboxId" option of the mailtrap+sdk transport (MAILTRAP_INBOX_ID in Laravel) is deprecated and will be removed in a future major release; use the "sandboxId" option (MAILTRAP_SANDBOX_ID) instead.',
            fn () => $this->getFactory()->create(
                new Dsn('mailtrap+sdk', AbstractApi::SENDMAIL_SANDBOX_HOST, self::USER, null, null, $options)
            )
        );

        $this->assertSame(sprintf('mailtrap+sdk://%s?sandboxId=1234', AbstractApi::SENDMAIL_SANDBOX_HOST), (string) $transport);
    }

    public static function deprecatedInboxIdOptionsProvider(): iterable
    {
        // MAILER_DSN=mailtrap+sdk://APIKEY@sandbox.api.mailtrap.io?inboxId=1234
        yield 'Symfony DSN query' => [['inboxId' => '1234']];

        // Laravel passes its "services.mailtrap-sdk" config as the options: here only MAILTRAP_INBOX_ID is set
        yield 'Laravel config' => [[
            'host' => AbstractApi::SENDMAIL_SANDBOX_HOST,
            'apiKey' => self::USER,
            'sandboxId' => null,
            'inboxId' => '1234',
        ]];

        yield 'empty sandboxId' => [['sandboxId' => '', 'inboxId' => '1234']];

        yield 'sandboxId and inboxId with the same value' => [['sandboxId' => '1234', 'inboxId' => '1234']];
    }

    /**
     * @dataProvider differentSandboxIdAndInboxIdOptionsProvider
     */
    #[DataProvider('differentSandboxIdAndInboxIdOptionsProvider')]
    public function testDifferentSandboxIdAndInboxIdOptions(array $options): void
    {
        $this->expectExceptionObject(new InvalidArgumentException(
            'The mailtrap+sdk transport received different values for the "sandboxId" and "inboxId" options (MAILTRAP_SANDBOX_ID and MAILTRAP_INBOX_ID in Laravel); set only "sandboxId".'
        ));

        $this->getFactory()->create(
            new Dsn('mailtrap+sdk', AbstractApi::SENDMAIL_SANDBOX_HOST, self::USER, null, null, $options)
        );
    }

    public static function differentSandboxIdAndInboxIdOptionsProvider(): iterable
    {
        yield 'two IDs' => [['sandboxId' => '1234', 'inboxId' => '5678']];
        yield 'zero sandboxId' => [['sandboxId' => '0', 'inboxId' => '1234']];
        yield 'zero inboxId' => [['sandboxId' => '1234', 'inboxId' => '0']];
    }

    /**
     * @dataProvider withoutSandboxIdOptionsProvider
     */
    #[DataProvider('withoutSandboxIdOptionsProvider')]
    public function testSandboxWithoutSandboxIdOption(array $options): void
    {
        $this->expectExceptionObject(new RuntimeException(
            'You cannot send an email to a sandbox with an empty "sandboxId" parameter. Example -> "MAILER_DSN=mailtrap+sdk://APIKEY@sandbox.api.mailtrap.io?sandboxId=1234"'
        ));

        $this->getFactory()->create(new Dsn('mailtrap+sdk', AbstractApi::SENDMAIL_SANDBOX_HOST, self::USER, null, null, $options));
    }

    public static function withoutSandboxIdOptionsProvider(): iterable
    {
        yield 'no options' => [[]];
        yield 'zero sandboxId' => [['sandboxId' => '0']];
        yield 'zero inboxId' => [['inboxId' => '0']];
    }

    public static function unsupportedSchemeProvider(): iterable
    {
        yield [
            new Dsn('mailtrap+foo', 'mailtrap', self::USER)
        ];
    }

    public static function incompleteDsnProvider(): iterable
    {
        yield [new Dsn('mailtrap+sdk', 'default')];
    }

    public static function unsupportedHostsProvider(): iterable
    {
        yield [new Dsn('mailtrap+sdk', 'invalid_url.api.mailtrap.io', self::USER)];
        yield [new Dsn('mailtrap+sdk', 'mailtrap.io', self::USER)];
    }
}
