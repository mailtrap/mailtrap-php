<?php

namespace Mailtrap\Tests\Api\Sandbox;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Sandbox\Sandbox;
use Mailtrap\DTO\Request\Sandbox as SandboxRequest;
use Mailtrap\Exception\HttpClientException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers Sandbox
 *
 * Class SandboxTest
 */
class SandboxTest extends MailtrapTestCase
{
    /**
     * @var Sandbox
     */
    private $sandbox;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sandbox = $this->getMockBuilder(Sandbox::class)
            ->onlyMethods(['httpGet', 'httpPost',  'httpPatch', 'httpDelete'])
            ->setConstructorArgs([$this->getConfigMock(), self::FAKE_ACCOUNT_ID])
            ->getMock()
        ;
    }

    protected function tearDown(): void
    {
        $this->sandbox = null;

        parent::tearDown();
    }

    public function testGetList(): void
    {
        $this->sandbox->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes')
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($this->getExpectedSandboxData())));

        $response = $this->sandbox->getList();
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertCount(2, $responseData);
        $this->assertArrayHasKey('id', array_shift($responseData));
    }

    public function testGetSandboxAttributes(): void
    {
        $this->sandbox->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_SANDBOX_ID)
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($this->getExpectedSandboxData()[0])));

        $response = $this->sandbox->getSandboxAttributes(self::FAKE_SANDBOX_ID);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(self::FAKE_SANDBOX_ID, $responseData['id']);
        $this->assertEquals('Admin Inbox', $responseData['name']);
        $this->assertCount(4, $responseData['permissions']);
    }

    public function testValidCreate(): void
    {
        $expectedData = [
            "id" => self::FAKE_SANDBOX_ID,
            "name" => "My new sandbox",
            "username" => "24a5f9a9947fc7",
            "password" => "a41e3846e78543",
            "max_size" => 600,
            "status" => "active",
            "email_username" => "d008137b46-4d932f",
            "email_username_enabled" => false,
            "sent_messages_count" => 0,
            "forwarded_messages_count" => 0,
            "used" => false,
            "forward_from_email_address" => "a3336-i3866@forward.mailtrap.info",
            "project_id" => 2424,
            "domain" => "localhost",
            "pop3_domain" => "localhost",
            "email_domain" => "localhost",
            "emails_count" => 0,
            "emails_unread_count" => 0,
            "last_message_sent_at" => null,
            "smtp_ports" => [
                25,
                465,
                587,
                2525
            ],
            "pop3_ports" => [
                1100,
                9950
            ],
            "max_message_size" => 15728640,
            "permissions" => [
                "can_read" => true,
                "can_update" => true,
                "can_destroy" => true,
                "can_leave" => false
            ]
        ];

        $this->sandbox->expects($this->once())
            ->method('httpPost')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/projects/' . self::FAKE_PROJECT_ID . '/inboxes',
                [],
                ['inbox' => ['name' => $expectedData['name']]]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedData)));

        $response = $this->sandbox->create(self::FAKE_PROJECT_ID, $expectedData['name']);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(self::FAKE_SANDBOX_ID, $responseData['id']);
    }

    public function testInvalidCreate(): void
    {
        $errorMsg = [
            "error" => [
                "name" => [
                    "can't be blank",
                ]
            ]
        ];
        $this->sandbox->expects($this->once())
            ->method('httpPost')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/projects/' . self::FAKE_PROJECT_ID . '/inboxes',
            )
            ->willReturn(new Response(422, ['Content-Type' => 'application/json'], json_encode($errorMsg)));

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage(
            'Errors: name -> can\'t be blank.'
        );

        $this->sandbox->create(self::FAKE_PROJECT_ID, 'a');
    }

    public function testValidDelete(): void
    {
        $this->sandbox->expects($this->once())
            ->method('httpDelete')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_SANDBOX_ID
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($this->getExpectedSandboxData()[0])));

        $response = $this->sandbox->delete(self::FAKE_SANDBOX_ID);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(self::FAKE_SANDBOX_ID, $responseData['id']);
    }

    public function testUpdate(): void
    {
        $newName = 'New sandbox name';
        $emailUsername = 'new-email-username';
        $sandboxRequest = new SandboxRequest($newName, $emailUsername);
        $expectedData = [
            "id" => self::FAKE_SANDBOX_ID,
            "name" => $newName,
            "username" => "24a5f9a9947fc7",
            "password" => "a41e3846e78543",
            "max_size" => 600,
            "status" => "active",
            "email_username" => $emailUsername,
            "email_username_enabled" => false,
            "sent_messages_count" => 0,
            "forwarded_messages_count" => 0,
            "used" => false,
            "forward_from_email_address" => "a3336-i3866@forward.mailtrap.info",
            "project_id" => 2424,
            "domain" => "localhost",
            "pop3_domain" => "localhost",
            "email_domain" => "localhost",
            "emails_count" => 0,
            "emails_unread_count" => 0,
            "last_message_sent_at" => null,
            "smtp_ports" => [
                25,
                465,
                587,
                2525
            ],
            "pop3_ports" => [
                1100,
                9950
            ],
            "max_message_size" => 15728640,
            "permissions" => [
                "can_read" => true,
                "can_update" => true,
                "can_destroy" => true,
                "can_leave" => false
            ]
        ];

        $this->sandbox->expects($this->once())
            ->method('httpPatch')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_SANDBOX_ID,
                [],
                ['inbox' => $sandboxRequest->toArray()]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedData)));

        $response = $this->sandbox->update(self::FAKE_SANDBOX_ID, $sandboxRequest);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(self::FAKE_SANDBOX_ID, $responseData['id']);
        $this->assertEquals($newName, $responseData['name']);
        $this->assertEquals($emailUsername, $responseData['email_username']);
    }

    public function testClean(): void
    {
        $this->sandbox->expects($this->once())
            ->method('httpPatch')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_SANDBOX_ID . '/clean',
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($this->getExpectedSandboxData()[0])));

        $response = $this->sandbox->clean(self::FAKE_SANDBOX_ID);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(self::FAKE_SANDBOX_ID, $responseData['id']);
    }

    public function testMarkAsRead(): void
    {
        $this->sandbox->expects($this->once())
            ->method('httpPatch')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_SANDBOX_ID . '/all_read',
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($this->getExpectedSandboxData()[0])));

        $response = $this->sandbox->markAsRead(self::FAKE_SANDBOX_ID);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(self::FAKE_SANDBOX_ID, $responseData['id']);
    }

    public function testResetSmtpCredentials(): void
    {
        $expectedData = $this->getExpectedSandboxData()[0];
        $expectedData['username'] = 'new_username';
        $expectedData['password'] = 'new_password';
        $this->sandbox->expects($this->once())
            ->method('httpPatch')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_SANDBOX_ID . '/reset_credentials',
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedData)));

        $response = $this->sandbox->resetSmtpCredentials(self::FAKE_SANDBOX_ID);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(self::FAKE_SANDBOX_ID, $responseData['id']);
        $this->assertEquals($expectedData['username'], $responseData['username']);
        $this->assertEquals($expectedData['password'], $responseData['password']);
    }

    public function testToggleEmailAddress(): void
    {
        $expectedData = $this->getExpectedSandboxData()[0];
        $expectedData['email_username_enabled'] = true;

        $this->sandbox->expects($this->once())
            ->method('httpPatch')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_SANDBOX_ID . '/toggle_email_username',
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedData)));

        $response = $this->sandbox->toggleEmailAddress(self::FAKE_SANDBOX_ID);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(self::FAKE_SANDBOX_ID, $responseData['id']);
        $this->assertEquals($expectedData['email_username_enabled'], $responseData['email_username_enabled']);
    }

    public function testResetEmailAddress(): void
    {
        $expectedData = $this->getExpectedSandboxData()[0];
        $expectedData['email_username'] = 'new_email_username';

        $this->sandbox->expects($this->once())
            ->method('httpPatch')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_SANDBOX_ID . '/reset_email_username',
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedData)));

        $response = $this->sandbox->resetEmailAddress(self::FAKE_SANDBOX_ID);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(self::FAKE_SANDBOX_ID, $responseData['id']);
        $this->assertEquals($expectedData['email_username'], $responseData['email_username']);
    }

    private function getExpectedSandboxData(): array
    {
        return [
            [
                "id" => self::FAKE_SANDBOX_ID,
                "name" => "Admin Inbox",
                "username" => "b3a87978452ae1",
                "password" => "6be9fcfc613a7c",
                "max_size" => 0,
                "status" => "active",
                "email_username" => "b7eae548c3-54c542",
                "email_username_enabled" => false,
                "sent_messages_count" => 52,
                "forwarded_messages_count" => 0,
                "used" => false,
                "forward_from_email_address" => "a3538-i4088@forward.mailtrap.info",
                "project_id" => 2293,
                "domain" => "localhost",
                "pop3_domain" => "localhost",
                "email_domain" => "localhost",
                "emails_count" => 0,
                "emails_unread_count" => 0,
                "last_message_sent_at" => null,
                "smtp_ports" => [
                    25,
                    465,
                    587,
                    2525
                ],
                "pop3_ports" => [
                    1100,
                    9950
                ],
                "max_message_size" => 5242880,
                "permissions" => [
                    "can_read" => true,
                    "can_update" => true,
                    "can_destroy" => true,
                    "can_leave" => true
                ]
            ],
            [
                "id" => 4089,
                "name" => "Viewer Inbox",
                "username" => "1d0aa0282f7712",
                "password" => "5667a20f611ae7",
                "max_size" => 0,
                "status" => "active",
                "email_username" => "f25402f8df-b77899",
                "email_username_enabled" => false,
                "sent_messages_count" => 527,
                "forwarded_messages_count" => 0,
                "used" => false,
                "forward_from_email_address" => "a3538-i4089@forward.mailtrap.info",
                "project_id" => 2293,
                "domain" => "localhost",
                "pop3_domain" => "localhost",
                "email_domain" => "localhost",
                "emails_count" => 0,
                "emails_unread_count" => 0,
                "last_message_sent_at" => null,
                "smtp_ports" => [
                    25,
                    465,
                    587,
                    2525
                ],
                "pop3_ports" => [
                    1100,
                    9950
                ],
                "max_message_size" => 5242880,
                "permissions" => [
                    "can_read" => true,
                    "can_update" => false,
                    "can_destroy" => false,
                    "can_leave" => true
                ]
            ]
        ];
    }
}
