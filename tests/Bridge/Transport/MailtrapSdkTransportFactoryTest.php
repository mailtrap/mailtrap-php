<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Bridge\Transport;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Bridge\Transport\MailtrapSdkTransport;
use Mailtrap\Bridge\Transport\MailtrapSdkTransportFactory;
use Mailtrap\Config;
use Mailtrap\Exception\RuntimeException;
use Mailtrap\MailtrapClient;
use Mailtrap\Tests\AssertsDeprecationsTrait;
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

    public function supportsProvider(): iterable
    {
        yield [
            new Dsn('mailtrap+sdk', 'default'),
            true,
        ];
    }

    public function createProvider(): iterable
    {
        $dispatcher = $this->getDispatcher();
        $logger = $this->getLogger();
        $psrClient = new Psr18Client($this->getClient());
        $sendConfig = (new Config(self::USER))
            ->setHttpClient($psrClient)
            ->setHost(AbstractApi::SENDMAIL_TRANSACTIONAL_HOST);
        $sandboxConfig = (new Config(self::USER))
            ->setHttpClient($psrClient)
            ->setHost(AbstractApi::SENDMAIL_SANDBOX_HOST);
        $bulkConfig = (new Config(self::USER))
            ->setHttpClient($psrClient)
            ->setHost(AbstractApi::SENDMAIL_BULK_HOST);
        $sandboxId = 1234;

        yield [
            new Dsn('mailtrap+sdk', 'default', self::USER),
            new MailtrapSdkTransport(
                (new MailtrapClient($sendConfig))->sending()->emails(),
                $sendConfig,
                $dispatcher,
                $logger
            ),
        ];

        yield [
            new Dsn('mailtrap+sdk', AbstractApi::SENDMAIL_TRANSACTIONAL_HOST, self::USER),
            new MailtrapSdkTransport(
                (new MailtrapClient($sendConfig))->sending()->emails(),
                $sendConfig,
                $dispatcher,
                $logger
            ),
        ];

        // sandbox
        yield [
            new Dsn('mailtrap+sdk', AbstractApi::SENDMAIL_SANDBOX_HOST, self::USER, null, null, ['sandboxId' => 1234]),
            new MailtrapSdkTransport(
                (new MailtrapClient($sandboxConfig))->sandbox()->emails($sandboxId),
                $sandboxConfig,
                $dispatcher,
                $logger
            ),
        ];

        // bulk sending
        yield [
            new Dsn('mailtrap+sdk', AbstractApi::SENDMAIL_BULK_HOST, self::USER),
            new MailtrapSdkTransport(
                (new MailtrapClient($bulkConfig))->bulkSending()->emails(),
                $bulkConfig,
                $dispatcher,
                $logger
            ),
        ];
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

    public function deprecatedInboxIdOptionsProvider(): iterable
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
    }

    public function testSandboxIdOptionTakesPrecedenceOverInboxId(): void
    {
        $transport = $this->getFactory()->create(new Dsn(
            'mailtrap+sdk',
            AbstractApi::SENDMAIL_SANDBOX_HOST,
            self::USER,
            null,
            null,
            ['sandboxId' => '1234', 'inboxId' => '5678']
        ));

        $this->assertSame(sprintf('mailtrap+sdk://%s?sandboxId=1234', AbstractApi::SENDMAIL_SANDBOX_HOST), (string) $transport);
    }

    public function testSandboxWithoutSandboxIdOption(): void
    {
        $this->expectExceptionObject(new RuntimeException(
            'You cannot send an email to a sandbox with an empty "sandboxId" parameter. Example -> "MAILER_DSN=mailtrap+sdk://APIKEY@sandbox.api.mailtrap.io?sandboxId=1234"'
        ));

        $this->getFactory()->create(new Dsn('mailtrap+sdk', AbstractApi::SENDMAIL_SANDBOX_HOST, self::USER));
    }

    public function unsupportedSchemeProvider(): iterable
    {
        yield [
            new Dsn('mailtrap+foo', 'mailtrap', self::USER)
        ];
    }

    public function incompleteDsnProvider(): iterable
    {
        yield [new Dsn('mailtrap+sdk', 'default')];
    }

    public function unsupportedHostsProvider(): iterable
    {
        yield [new Dsn('mailtrap+sdk', 'invalid_url.api.mailtrap.io', self::USER)];
        yield [new Dsn('mailtrap+sdk', 'mailtrap.io', self::USER)];
    }
}
