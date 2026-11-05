<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\Sandbox;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Sandbox\Inbox;
use Mailtrap\Api\Sandbox\Sandbox;
use Mailtrap\DTO\Request\Inbox as InboxRequest;
use Mailtrap\DTO\Request\Sandbox as SandboxRequest;
use Mailtrap\Tests\AssertsDeprecationsTrait;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * Backward compatibility of the deprecated Inbox API and request, replaced by Sandbox.
 *
 * @covers Inbox
 *
 * Class InboxTest
 */
class InboxTest extends MailtrapTestCase
{
    use AssertsDeprecationsTrait;

    private ?Inbox $inbox;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inbox = $this->getMockBuilder(Inbox::class)
            ->onlyMethods(['httpGet', 'httpPost', 'httpPatch', 'httpDelete'])
            ->setConstructorArgs([$this->getConfigMock(), self::FAKE_ACCOUNT_ID])
            ->getMock()
        ;
    }

    protected function tearDown(): void
    {
        $this->inbox = null;

        parent::tearDown();
    }

    public function testConstructorIsDeprecated(): void
    {
        $inbox = $this->assertDeprecationTriggered(
            'Mailtrap\Api\Sandbox\Inbox (MailtrapSandboxClient::inboxes()) is deprecated and will be removed in a future major release; use Mailtrap\Api\Sandbox\Sandbox (MailtrapSandboxClient::sandboxes()) instead.',
            fn () => new Inbox($this->getConfigMock(), self::FAKE_ACCOUNT_ID)
        );

        $this->assertInstanceOf(Sandbox::class, $inbox);
        $this->assertSame(self::FAKE_ACCOUNT_ID, $inbox->getAccountId());
    }

    public function testGetInboxAttributes(): void
    {
        $this->inbox->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_SANDBOX_ID)
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode(['id' => self::FAKE_SANDBOX_ID])));

        $response = $this->assertDeprecationTriggered(
            'Inbox::getInboxAttributes() is deprecated and will be removed in a future major release; use Mailtrap\Api\Sandbox\Sandbox::getSandboxAttributes() instead.',
            fn () => $this->inbox->getInboxAttributes(self::FAKE_SANDBOX_ID)
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testCreateKeepsOldNamedArguments(): void
    {
        $this->inbox->expects($this->once())
            ->method('httpPost')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/projects/' . self::FAKE_PROJECT_ID . '/inboxes',
                [],
                ['inbox' => ['name' => 'My new inbox']]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode(['id' => self::FAKE_SANDBOX_ID])));

        $response = $this->inbox->create(projectId: self::FAKE_PROJECT_ID, inboxName: 'My new inbox');

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testUpdateKeepsOldNamedArgumentsAndAcceptsInboxRequest(): void
    {
        $inboxRequest = $this->assertDeprecationTriggered(
            'Mailtrap\DTO\Request\Inbox is deprecated and will be removed in a future major release; use Mailtrap\DTO\Request\Sandbox instead.',
            fn () => new InboxRequest('New inbox name', 'new-email-username')
        );
        $this->assertInstanceOf(SandboxRequest::class, $inboxRequest);

        $this->inbox->expects($this->once())
            ->method('httpPatch')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_SANDBOX_ID,
                [],
                ['inbox' => ['name' => 'New inbox name', 'email_username' => 'new-email-username']]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode(['id' => self::FAKE_SANDBOX_ID])));

        $response = $this->inbox->update(inboxId: self::FAKE_SANDBOX_ID, updateInbox: $inboxRequest);

        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @dataProvider inboxIdMethodsProvider
     */
    public function testMethodsKeepOldInboxIdNamedArgument(string $method, string $httpMethod, string $pathSuffix): void
    {
        $this->inbox->expects($this->once())
            ->method($httpMethod)
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_SANDBOX_ID . $pathSuffix)
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode(['id' => self::FAKE_SANDBOX_ID])));

        $response = $this->inbox->{$method}(inboxId: self::FAKE_SANDBOX_ID);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function inboxIdMethodsProvider(): iterable
    {
        yield ['delete', 'httpDelete', ''];
        yield ['clean', 'httpPatch', '/clean'];
        yield ['markAsRead', 'httpPatch', '/all_read'];
        yield ['resetSmtpCredentials', 'httpPatch', '/reset_credentials'];
        yield ['toggleEmailAddress', 'httpPatch', '/toggle_email_username'];
        yield ['resetEmailAddress', 'httpPatch', '/reset_email_username'];
    }
}
