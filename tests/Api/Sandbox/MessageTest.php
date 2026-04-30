<?php

namespace Mailtrap\Tests\Api\Sandbox;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Sandbox\Message;
use Mailtrap\Exception\HttpClientException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers Message
 *
 * Class MessageTest
 */
class MessageTest extends MailtrapTestCase
{
    /**
     * @var Message
     */
    private $message;

    protected function setUp(): void
    {
        parent::setUp();

        $this->message = $this->getMockBuilder(Message::class)
            ->onlyMethods(['httpGet', 'httpPost', 'httpPatch', 'httpDelete'])
            ->setConstructorArgs([$this->getConfigMock(), self::FAKE_ACCOUNT_ID, self::FAKE_INBOX_ID])
            ->getMock()
        ;
    }

    protected function tearDown(): void
    {
        $this->message = null;

        parent::tearDown();
    }

    public function testValidGetList(): void
    {
        $this->message->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_INBOX_ID . '/messages',
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($this->getMessagesData())));

        $response = $this->message->getList();
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertCount(2, $responseData);
        $this->assertArrayHasKey('subject', array_shift($responseData));
    }

    public function testValidGetListWithFilters(): void
    {
        $page = 1;
        $search = 'test';

        $expectedData = [$this->getMessagesData()[0]];
        $this->message->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_INBOX_ID . '/messages',
                [
                    'page' => $page,
                    'search' => $search,
                ]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedData)));

        $response = $this->message->getList(
            $page,
            $search,
        );
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertCount(1, $responseData);

        $responseData = array_shift($responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(self::FAKE_MESSAGE_ID, $responseData['id']);
        $this->assertEquals(self::FAKE_INBOX_ID, $responseData['inbox_id']);
    }

    public function testGetById(): void
    {
        $this->message->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_INBOX_ID . '/messages/' . self::FAKE_MESSAGE_ID,
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($this->getMessagesData()[0])));

        $response = $this->message->getById(self::FAKE_MESSAGE_ID);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(self::FAKE_MESSAGE_ID, $responseData['id']);
        $this->assertEquals(self::FAKE_INBOX_ID, $responseData['inbox_id']);
    }

    /**
     * @dataProvider spamScoreProvider
     */
    public function testGetSpamScore($expectedData): void
    {
        $this->message->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_INBOX_ID . '/messages/' . self::FAKE_MESSAGE_ID . '/spam_report',
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedData)));

        $response = $this->message->getSpamScore(self::FAKE_MESSAGE_ID);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('ResponseMessage', $responseData);
    }

    /**
     * @dataProvider htmlAnalysisProvider
     */
    public function testGetHtmlAnalysis($expectedData): void
    {
        $this->message->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_INBOX_ID . '/messages/' . self::FAKE_MESSAGE_ID . '/analyze',
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedData)));

        $response = $this->message->getHtmlAnalysis(self::FAKE_MESSAGE_ID);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testGetMailHeaders(): void
    {
        $expectedHeaders = [
            'headers' => [
                'from' => 'sender@example.com',
                'to' => 'recipient@example.com',
                'subject' => 'Test message',
            ],
        ];

        $this->message->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_INBOX_ID . '/messages/' . self::FAKE_MESSAGE_ID . '/mail_headers',
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedHeaders)));

        $response = $this->message->getMailHeaders(self::FAKE_MESSAGE_ID);
        $responseData = ResponseHelper::toArray($response);

        $this->assertArrayHasKey('headers', $responseData);
        $this->assertEquals('sender@example.com', $responseData['headers']['from']);
    }

    public function testForward(): void
    {
        $email = 'recipient@example.com';

        $this->message->expects($this->once())
            ->method('httpPost')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_INBOX_ID . '/messages/' . self::FAKE_MESSAGE_ID . '/forward',
                [],
                ['email' => $email]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode(['message' => 'Your email message has been successfully forwarded'])));

        $response = $this->message->forward(self::FAKE_MESSAGE_ID, $email);
        $responseData = ResponseHelper::toArray($response);

        $this->assertArrayHasKey('message', $responseData);
    }

    public function testForwardFailsWithUnverifiedRecipient(): void
    {
        $email = 'unverified@example.com';

        $this->message->expects($this->once())
            ->method('httpPost')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_INBOX_ID . '/messages/' . self::FAKE_MESSAGE_ID . '/forward',
                [],
                ['email' => $email]
            )
            ->willReturn(new Response(422, ['Content-Type' => 'application/json'], json_encode(['errors' => ['base' => ['Recipient email must be verified']]])));

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('Recipient email must be verified');

        $this->message->forward(self::FAKE_MESSAGE_ID, $email);
    }

    public function testGetText(): void
    {
        $expectedData = 'Congrats for sending test email with Mailtrap!';
        $this->message->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_INBOX_ID . '/messages/' . self::FAKE_MESSAGE_ID . '/body.txt',
            )
            ->willReturn(new Response(200, ['Content-Type' => 'text/plain'], $expectedData));

        $response = $this->message->getText(self::FAKE_MESSAGE_ID);
        $responseData = ResponseHelper::toString($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($expectedData, $responseData);
    }

    public function testGetRaw(): void
    {
        $expectedData = 'From: Magic Elves <from@example.com> To: Mailtrap Inbox <to@example.com> ...';
        $this->message->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_INBOX_ID . '/messages/' . self::FAKE_MESSAGE_ID . '/body.raw',
            )
            ->willReturn(new Response(200, ['Content-Type' => 'text/plain'], $expectedData));

        $response = $this->message->getRaw(self::FAKE_MESSAGE_ID);
        $responseData = ResponseHelper::toString($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($expectedData, $responseData);
    }

    public function testGetHtml(): void
    {
        $expectedData = '<!doctype html> <html> <head> <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> </head> ...';
        $this->message->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_INBOX_ID . '/messages/' . self::FAKE_MESSAGE_ID . '/body.html',
            )
            ->willReturn(new Response(200, ['Content-Type' => 'text/html'], $expectedData));

        $response = $this->message->getHtml(self::FAKE_MESSAGE_ID);
        $responseData = ResponseHelper::toString($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($expectedData, $responseData);
    }

    public function testGetEml(): void
    {
        $expectedData = 'From: Magic Elves <from@example.com> To: Mailtrap Inbox <to@example.com> ...';
        $this->message->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_INBOX_ID . '/messages/' . self::FAKE_MESSAGE_ID . '/body.eml',
            )
            ->willReturn(new Response(200, ['Content-Type' => 'message/rfc822'], $expectedData));

        $response = $this->message->getEml(self::FAKE_MESSAGE_ID);
        $responseData = ResponseHelper::toString($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($expectedData, $responseData);
    }

    public function testGeSource(): void
    {
        $expectedData = '<!doctype html> <html> <head> <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> </head> ...';
        $this->message->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_INBOX_ID . '/messages/' . self::FAKE_MESSAGE_ID . '/body.htmlsource',
            )
            ->willReturn(new Response(200, ['Content-Type' => 'text/html'], $expectedData));

        $response = $this->message->getSource(self::FAKE_MESSAGE_ID);
        $responseData = ResponseHelper::toString($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($expectedData, $responseData);
    }

    /**
     * @dataProvider markAsReadProvider
     */
    public function testMarkAsRead($expectedData, $isRead): void
    {
        $this->message->expects($this->once())
            ->method('httpPatch')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_INBOX_ID . '/messages/' . self::FAKE_MESSAGE_ID,
                [],
                [
                    'message' => [
                        'is_read' => $expectedData['is_read']
                    ]
                ]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedData)));

        $response = $this->message->markAsRead(self::FAKE_MESSAGE_ID, $expectedData['is_read']);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('is_read', $responseData);
        $this->assertEquals($isRead, $responseData['is_read']);
    }

    public function testDelete(): void
    {
        $this->message->expects($this->once())
            ->method('httpDelete')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/inboxes/' . self::FAKE_INBOX_ID . '/messages/' . self::FAKE_MESSAGE_ID,
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($this->getMessagesData()[0])));

        $response = $this->message->delete(self::FAKE_MESSAGE_ID);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(self::FAKE_MESSAGE_ID, $responseData['id']);
        $this->assertEquals(self::FAKE_INBOX_ID, $responseData['inbox_id']);
    }

    public function spamScoreProvider(): array
    {
        return [
            [
                "report" => [
                    "ResponseCode" => 2,
                    "ResponseMessage" => "Not spam",
                    "ResponseVersion" => "1.2",
                    "Score" => 1.2,
                    "Spam" => false,
                    "Threshold" => 5,
                    "Details" => [
                    ]
                ]
            ]
        ];
    }

    public function markAsReadProvider(): array
    {
        return [
            [
                $this->getMessagesData()[0],
                true,
            ],
            [
                $this->getMessagesData()[1],
                false,
            ],
        ];
    }

    public function htmlAnalysisProvider(): array
    {
        return [
            [
                "report" => [
                    "status" => "success",
                    "errors" => [
                        [
                            "error_line" => 15
                        ],
                        [
                            "rule_name" => "style"
                        ],
                        [
                            "email_clients" => [
                                "desktop" => [
                                    "Notes 6 / 7"
                                ],
                                "mobile" => [
                                    "Gmail"
                                ]
                            ]
                        ],
                        [
                            "error_line" => 7,
                            "rule_name" => "display",
                            "email_clients" => [
                                "desktop" => [
                                    "AOL Desktop",
                                    "IBM Notes 9",
                                    "Outlook 2000–03",
                                    "Outlook 2007–16",
                                    "Outlook Express",
                                    "Postbox",
                                    "Windows 10 Mail",
                                    "Windows Live Mail"
                                ],
                                "mobile" => [
                                    "Android 4.2.2 Mail",
                                    "Android 4.4.4 Mail",
                                    "BlackBerry",
                                    "Gmail Android app IMAP",
                                    "Google Inbox Android app",
                                    "Windows Phone 8 Mail",
                                    "Yahoo! Mail Android app",
                                    "Yahoo! Mail iOS app"
                                ],
                                "web" => [
                                    "Yahoo! Mail"
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    private function getMessagesData(): array
    {
        return [
            [
                "id" => self::FAKE_MESSAGE_ID,
                "inbox_id" => self::FAKE_INBOX_ID,
                "subject" => "Test email",
                "sent_at" => "2022-08-19T11:34:33.839Z",
                "from_email" => "per667son25@feeney.biz",
                "from_name" => "Ela Marks",
                "to_email" => "per688son26@donnelly.info",
                "to_name" => "Edmund Maggio",
                "email_size" => 300,
                "is_read" => true,
                "created_at" => "2022-08-19T11:34:33.841Z",
                "updated_at" => "2022-08-19T11:34:33.841Z",
                "html_body_size" => 150,
                "text_body_size" => 100,
                "human_size" => "300 Bytes",
                "html_path" => "/api/accounts/336/inboxes/342/messages/92/body.html",
                "txt_path" => "/api/accounts/336/inboxes/342/messages/92/body.txt",
                "raw_path" => "/api/accounts/336/inboxes/342/messages/92/body.raw",
                "download_path" => "/api/accounts/336/inboxes/342/messages/92/body.eml",
                "html_source_path" => "/api/accounts/336/inboxes/342/messages/92/body.htmlsource",
                "blacklists_report_info" => false,
                "smtp_information" => [
                    "ok" => true,
                    "data" => [
                        "mail_from_addr" => "per667son25@feeney.biz",
                        "client_ip" => "75.180.183.201"
                    ]
                ]
            ], [
                "id" => 1111,
                "inbox_id" => 222,
                "subject" => "fffff",
                "sent_at" => "2022-08-19T11:34:33.839Z",
                "from_email" => "per667son25@feeney.biz",
                "from_name" => "Ela Marks",
                "to_email" => "per688son26@donnelly.info",
                "to_name" => "Edmund Maggio",
                "email_size" => 300,
                "is_read" => false,
                "created_at" => "2022-08-19T11:34:33.841Z",
                "updated_at" => "2022-08-19T11:34:33.841Z",
                "html_body_size" => 150,
                "text_body_size" => 100,
                "human_size" => "300 Bytes",
                "html_path" => "/api/accounts/336/inboxes/222/messages/1111/body.html",
                "txt_path" => "/api/accounts/336/inboxes/222/messages/1111/body.txt",
                "raw_path" => "/api/accounts/336/inboxes/222/messages/1111/body.raw",
                "download_path" => "/api/accounts/336/inboxes/342/messages/92/body.eml",
                "html_source_path" => "/api/accounts/336/inboxes/342/messages/92/body.htmlsource",
                "blacklists_report_info" => false,
                "smtp_information" => [
                    "ok" => true,
                    "data" => [
                        "mail_from_addr" => "per667son25@feeney.biz",
                        "client_ip" => "75.180.183.201"
                    ]
                ]
            ]
        ];
    }
}
