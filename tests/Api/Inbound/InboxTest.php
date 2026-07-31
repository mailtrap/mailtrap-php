<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\Inbound;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Inbound\Inbox as InboxApi;
use Mailtrap\DTO\Request\Inbound\CreateInboundInbox;
use Mailtrap\DTO\Request\Inbound\UpdateInboundInbox;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers \Mailtrap\Api\Inbound\Inbox
 *
 * Class InboxTest
 */
class InboxTest extends MailtrapTestCase
{
    private const FAKE_FOLDER_ID = 42;
    private const INBOX_ID = 9;
    private const BASE_URL = AbstractApi::DEFAULT_HOST . '/api/inbound/folders/' . self::FAKE_FOLDER_ID . '/inboxes';

    private ?InboxApi $inbox;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inbox = $this->getMockBuilder(InboxApi::class)
            ->onlyMethods(['httpGet', 'httpPost', 'httpPatch', 'httpDelete'])
            ->setConstructorArgs([$this->getConfigMock(), self::FAKE_FOLDER_ID])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->inbox = null;
        parent::tearDown();
    }

    private function inboxResponseBody(): array
    {
        return [
            'id' => self::INBOX_ID,
            'name' => 'Tickets',
            'address' => 'tickets@inbound.example.com',
            'domain_id' => 3,
        ];
    }

    public function testGetList(): void
    {
        $this->inbox->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_URL)
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode([$this->inboxResponseBody()])
            ));

        $data = ResponseHelper::toArray($this->inbox->getList());

        $this->assertSame(self::INBOX_ID, $data[0]['id']);
    }

    public function testGetById(): void
    {
        $this->inbox->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_URL . '/' . self::INBOX_ID)
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode($this->inboxResponseBody())
            ));

        $data = ResponseHelper::toArray($this->inbox->getById(self::INBOX_ID));

        $this->assertSame('tickets@inbound.example.com', $data['address']);
    }

    public function testCreateWithDomainId(): void
    {
        $this->inbox->expects($this->once())
            ->method('httpPost')
            ->with(self::BASE_URL, [], ['name' => 'Tickets', 'domain_id' => 3])
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode($this->inboxResponseBody())
            ));

        $data = ResponseHelper::toArray(
            $this->inbox->create(new CreateInboundInbox('Tickets', 3))
        );

        $this->assertSame(self::INBOX_ID, $data['id']);
    }

    public function testCreateOmitsDomainIdWhenNull(): void
    {
        $this->inbox->expects($this->once())
            ->method('httpPost')
            ->with(self::BASE_URL, [], ['name' => 'Tickets'])
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode($this->inboxResponseBody())
            ));

        $this->inbox->create(new CreateInboundInbox('Tickets'));
    }

    public function testUpdate(): void
    {
        $this->inbox->expects($this->once())
            ->method('httpPatch')
            ->with(self::BASE_URL . '/' . self::INBOX_ID, [], ['name' => 'Renamed'])
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['id' => self::INBOX_ID, 'name' => 'Renamed'])
            ));

        $data = ResponseHelper::toArray(
            $this->inbox->update(self::INBOX_ID, new UpdateInboundInbox('Renamed'))
        );

        $this->assertSame('Renamed', $data['name']);
    }

    public function testDelete(): void
    {
        $this->inbox->expects($this->once())
            ->method('httpDelete')
            ->with(self::BASE_URL . '/' . self::INBOX_ID)
            ->willReturn(new Response(204));

        $this->assertSame(204, $this->inbox->delete(self::INBOX_ID)->getStatusCode());
    }
}
