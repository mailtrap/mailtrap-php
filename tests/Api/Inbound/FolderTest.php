<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\Inbound;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Inbound\Folder as FolderApi;
use Mailtrap\DTO\Request\Inbound\CreateInboundFolder;
use Mailtrap\DTO\Request\Inbound\UpdateInboundFolder;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers \Mailtrap\Api\Inbound\Folder
 *
 * Class FolderTest
 */
class FolderTest extends MailtrapTestCase
{
    private const FAKE_FOLDER_ID = 42;
    private const BASE_URL = AbstractApi::DEFAULT_HOST . '/api/inbound/folders';

    private ?FolderApi $folder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->folder = $this->getMockBuilder(FolderApi::class)
            ->onlyMethods(['httpGet', 'httpPost', 'httpPatch', 'httpDelete'])
            ->setConstructorArgs([$this->getConfigMock()])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->folder = null;
        parent::tearDown();
    }

    public function testGetList(): void
    {
        $this->folder->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_URL)
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode([['id' => self::FAKE_FOLDER_ID, 'name' => 'Support']])
            ));

        $data = ResponseHelper::toArray($this->folder->getList());

        $this->assertSame(self::FAKE_FOLDER_ID, $data[0]['id']);
        $this->assertSame('Support', $data[0]['name']);
    }

    public function testGetById(): void
    {
        $this->folder->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_URL . '/' . self::FAKE_FOLDER_ID)
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['id' => self::FAKE_FOLDER_ID, 'name' => 'Support'])
            ));

        $data = ResponseHelper::toArray($this->folder->getById(self::FAKE_FOLDER_ID));

        $this->assertSame(self::FAKE_FOLDER_ID, $data['id']);
    }

    public function testCreateSendsFlatBody(): void
    {
        $this->folder->expects($this->once())
            ->method('httpPost')
            ->with(self::BASE_URL, [], ['name' => 'Support'])
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['id' => self::FAKE_FOLDER_ID, 'name' => 'Support'])
            ));

        $data = ResponseHelper::toArray($this->folder->create(new CreateInboundFolder('Support')));

        $this->assertSame('Support', $data['name']);
    }

    public function testUpdate(): void
    {
        $this->folder->expects($this->once())
            ->method('httpPatch')
            ->with(self::BASE_URL . '/' . self::FAKE_FOLDER_ID, [], ['name' => 'Renamed'])
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['id' => self::FAKE_FOLDER_ID, 'name' => 'Renamed'])
            ));

        $data = ResponseHelper::toArray(
            $this->folder->update(self::FAKE_FOLDER_ID, new UpdateInboundFolder('Renamed'))
        );

        $this->assertSame('Renamed', $data['name']);
    }

    public function testDelete(): void
    {
        $this->folder->expects($this->once())
            ->method('httpDelete')
            ->with(self::BASE_URL . '/' . self::FAKE_FOLDER_ID)
            ->willReturn(new Response(204));

        $this->assertSame(204, $this->folder->delete(self::FAKE_FOLDER_ID)->getStatusCode());
    }
}
