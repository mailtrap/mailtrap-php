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

    public function testDelete(): void
    {
        $this->thread->expects($this->once())
            ->method('httpDelete')
            ->with(self::BASE_URL . '/' . self::FAKE_THREAD_ID)
            ->willReturn(new Response(204));

        $this->assertSame(204, $this->thread->delete(self::FAKE_THREAD_ID)->getStatusCode());
    }
}
