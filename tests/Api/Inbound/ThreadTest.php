<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\Inbound;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Inbound\Thread as ThreadApi;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers \Mailtrap\Api\Inbound\Thread
 *
 * Class ThreadTest
 */
class ThreadTest extends MailtrapTestCase
{
    private const FAKE_THREAD_ID = '1871574677878845504';
    private const BASE_URL = AbstractApi::DEFAULT_HOST . '/api/inbound/inboxes/' . self::FAKE_INBOX_ID . '/threads';

    private ?ThreadApi $thread;

    protected function setUp(): void
    {
        parent::setUp();
        $this->thread = $this->getMockBuilder(ThreadApi::class)
            ->onlyMethods(['httpGet', 'httpDelete'])
            ->setConstructorArgs([$this->getConfigMock(), self::FAKE_INBOX_ID])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->thread = null;
        parent::tearDown();
    }

    public function testGetList(): void
    {
        $this->thread->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_URL, [])
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['data' => [['id' => self::FAKE_THREAD_ID, 'message_count' => 2]], 'total_count' => 1, 'last_id' => self::FAKE_THREAD_ID])
            ));

        $data = ResponseHelper::toArray($this->thread->getList());

        $this->assertSame(2, $data['data'][0]['message_count']);
    }

    public function testGetListPassesLastIdCursor(): void
    {
        $this->thread->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_URL, ['last_id' => 'cursor-1'])
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['data' => [], 'total_count' => 0, 'last_id' => null])
            ));

        $this->thread->getList('cursor-1');
    }

    public function testGetListPassesSearch(): void
    {
        $this->thread->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_URL, ['search' => 'acme'])
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['data' => [], 'total_count' => 0, 'last_id' => null])
            ));

        $this->thread->getList(search: 'acme');
    }

    public function testGetListPassesSearchTogetherWithLastIdCursor(): void
    {
        $this->thread->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_URL, ['last_id' => 'WzE3NzgyNDE5MDAwMDAsIjE3MDAwMDAwMDAwMDAxMjMiXQ==', 'search' => 'acme'])
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['data' => [], 'total_count' => 0, 'last_id' => null])
            ));

        $this->thread->getList('WzE3NzgyNDE5MDAwMDAsIjE3MDAwMDAwMDAwMDAxMjMiXQ==', 'acme');
    }

    public function testGetById(): void
    {
        $this->thread->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_URL . '/' . self::FAKE_THREAD_ID)
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['id' => self::FAKE_THREAD_ID, 'messages' => [['direction' => 'inbound']]])
            ));

        $data = ResponseHelper::toArray($this->thread->getById(self::FAKE_THREAD_ID));

        $this->assertSame('inbound', $data['messages'][0]['direction']);
    }

    public function testGetByIdExposesForwardsAndDelivery(): void
    {
        $this->thread->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_URL . '/' . self::FAKE_THREAD_ID)
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'id' => self::FAKE_THREAD_ID,
                    'messages' => [
                        [
                            'direction' => 'inbound',
                            'visibility_status' => 'available',
                            'forwards' => [
                                [
                                    'rule_id' => 7,
                                    'rule_name' => 'Copy to support team',
                                    'destination' => 'team@example.com',
                                    'status' => 'forwarded',
                                    'reason' => null,
                                    'message_id' => 'f47ac10b-58cc-4372-a567-0e02b2c3d479',
                                ],
                            ],
                        ],
                        [
                            'direction' => 'outbound',
                            'visibility_status' => 'available',
                            'delivery' => [
                                'to' => 'customer@example.com',
                                'status' => 'delivered',
                                'delivered_at' => '2026-05-08T11:40:05.000Z',
                                'bounced_at' => null,
                            ],
                        ],
                    ],
                ])
            ));

        $data = ResponseHelper::toArray($this->thread->getById(self::FAKE_THREAD_ID));

        $forward = $data['messages'][0]['forwards'][0];
        $this->assertSame(7, $forward['rule_id']);
        $this->assertSame('forwarded', $forward['status']);
        $this->assertNull($forward['reason']);

        $delivery = $data['messages'][1]['delivery'];
        $this->assertSame('customer@example.com', $delivery['to']);
        $this->assertSame('delivered', $delivery['status']);
        $this->assertSame('2026-05-08T11:40:05.000Z', $delivery['delivered_at']);
        $this->assertNull($delivery['bounced_at']);
    }

    public function testDelete(): void
    {
        $this->thread->expects($this->once())
            ->method('httpDelete')
            ->with(self::BASE_URL . '/' . self::FAKE_THREAD_ID)
            ->willReturn(new Response(204));

        $this->assertSame(204, $this->thread->delete(self::FAKE_THREAD_ID)->getStatusCode());
    }
}
