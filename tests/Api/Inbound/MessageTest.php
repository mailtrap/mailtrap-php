<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\Inbound;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Inbound\Message as MessageApi;
use Mailtrap\Exception\InvalidArgumentException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * @covers \Mailtrap\Api\Inbound\Message
 *
 * Class MessageTest
 */
class MessageTest extends MailtrapTestCase
{
    private const MESSAGE_ID = '1871574677877796928';
    private const BASE_URL = AbstractApi::DEFAULT_HOST . '/api/inbound/inboxes/' . self::FAKE_INBOX_ID . '/messages';

    private ?MessageApi $message;

    protected function setUp(): void
    {
        parent::setUp();
        $this->message = $this->getMockBuilder(MessageApi::class)
            ->onlyMethods(['httpGet', 'httpPost', 'httpDelete'])
            ->setConstructorArgs([$this->getConfigMock(), self::FAKE_INBOX_ID])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->message = null;
        parent::tearDown();
    }

    public function testGetList(): void
    {
        $this->message->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_URL, [])
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['data' => [['id' => self::MESSAGE_ID]], 'total_count' => 1, 'last_id' => self::MESSAGE_ID])
            ));

        $data = ResponseHelper::toArray($this->message->getList());

        $this->assertSame(1, $data['total_count']);
        $this->assertSame(self::MESSAGE_ID, $data['data'][0]['id']);
    }

    public function testGetListPassesLastIdCursor(): void
    {
        $this->message->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_URL, ['last_id' => 'cursor-1'])
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['data' => [], 'total_count' => 0, 'last_id' => null])
            ));

        $this->message->getList('cursor-1');
    }

    public function testGetById(): void
    {
        $this->message->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_URL . '/' . self::MESSAGE_ID)
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['id' => self::MESSAGE_ID, 'html_body' => '<p>Hi</p>'])
            ));

        $data = ResponseHelper::toArray($this->message->getById(self::MESSAGE_ID));

        $this->assertSame('<p>Hi</p>', $data['html_body']);
    }

    public function testDelete(): void
    {
        $this->message->expects($this->once())
            ->method('httpDelete')
            ->with(self::BASE_URL . '/' . self::MESSAGE_ID)
            ->willReturn(new Response(204));

        $this->assertSame(204, $this->message->delete(self::MESSAGE_ID)->getStatusCode());
    }

    public function testReplyMapsEmailToPayload(): void
    {
        $email = (new Email())
            ->from(new Address('support@example.com'))
            ->text('Thanks!');

        $this->message->expects($this->once())
            ->method('httpPost')
            ->with(
                self::BASE_URL . '/' . self::MESSAGE_ID . '/reply',
                [],
                ['from' => ['email' => 'support@example.com'], 'text' => 'Thanks!']
            )
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['message_ids' => ['s1']])
            ));

        $data = ResponseHelper::toArray($this->message->reply(self::MESSAGE_ID, $email));

        $this->assertSame(['s1'], $data['message_ids']);
    }

    public function testReplyAll(): void
    {
        $email = (new Email())->text('All');

        $this->message->expects($this->once())
            ->method('httpPost')
            ->with(self::BASE_URL . '/' . self::MESSAGE_ID . '/reply_all', [], ['text' => 'All'])
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['message_ids' => ['s1', 's2']])
            ));

        $this->message->replyAll(self::MESSAGE_ID, $email);
    }

    public function testForwardSerializesRecipients(): void
    {
        $email = (new Email())->to(new Address('colleague@example.com'))->text('FYI');

        $this->message->expects($this->once())
            ->method('httpPost')
            ->with(
                self::BASE_URL . '/' . self::MESSAGE_ID . '/forward',
                [],
                ['to' => [['email' => 'colleague@example.com']], 'text' => 'FYI']
            )
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['message_ids' => ['s1']])
            ));

        $this->message->forward(self::MESSAGE_ID, $email);
    }

    public function testForwardWithoutRecipientThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->message->forward(self::MESSAGE_ID, (new Email())->text('FYI'));
    }

    public function testForwardRequiresToRecipientNotCcOrBcc(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // cc / bcc alone are not enough — forward needs at least one "to".
        $email = (new Email())->cc(new Address('cc@example.com'))->text('FYI');

        $this->message->forward(self::MESSAGE_ID, $email);
    }
}
