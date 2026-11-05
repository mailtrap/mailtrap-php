<?php

namespace Mailtrap\Tests\Api\Sandbox;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Sandbox\Attachment;
use Mailtrap\Exception\HttpClientException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers Attachment
 *
 * Class ProjectTest
 */
class AttachmentTest extends MailtrapTestCase
{
    /**
     * @var Attachment
     */
    private $attachment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attachment = $this->getMockBuilder(Attachment::class)
            ->onlyMethods(['httpGet'])
            ->setConstructorArgs([$this->getConfigMock(), self::FAKE_ACCOUNT_ID, self::FAKE_SANDBOX_ID])
            ->getMock()
        ;
    }

    protected function tearDown(): void
    {
        $this->attachment = null;

        parent::tearDown();
    }

    public function testGetMessageAttachments(): void
    {
        $attachmentType = 'inline';
        $this->attachment->expects($this->once())
            ->method('httpGet')
            ->with(
                sprintf(
                    '%s/api/accounts/%s/inboxes/%s/messages/%s/attachments',
                    AbstractApi::DEFAULT_HOST,
                    self::FAKE_ACCOUNT_ID,
                    self::FAKE_SANDBOX_ID,
                    self::FAKE_MESSAGE_ID,
                ),
                [
                    'attachment_type' => $attachmentType
                ]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($this->getExpectedData())));

        $response = $this->attachment->getMessageAttachments(
            self::FAKE_MESSAGE_ID,
            $attachmentType
        );
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertCount(1, $responseData);
        $this->assertArrayHasKey('filename', array_shift($responseData));
    }

    public function testGetMessageAttachment(): void
    {
        $this->attachment->expects($this->once())
            ->method('httpGet')
            ->with(sprintf(
                '%s/api/accounts/%s/inboxes/%s/messages/%s/attachments/%s',
                AbstractApi::DEFAULT_HOST,
                self::FAKE_ACCOUNT_ID,
                self::FAKE_SANDBOX_ID,
                self::FAKE_MESSAGE_ID,
                self::FAKE_MESSAGE_ATTACHMENT_ID
            ))
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($this->getExpectedData())));

        $response = $this->attachment->getMessageAttachment(
            self::FAKE_MESSAGE_ID,
            self::FAKE_MESSAGE_ATTACHMENT_ID
        );
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertCount(1, $responseData);
        $this->assertArrayHasKey('filename', array_shift($responseData));
    }

    private function getExpectedData(): array
    {
        return [
            [
                "id" => self::FAKE_MESSAGE_ATTACHMENT_ID,
                "message_id" => self::FAKE_MESSAGE_ID,
                "filename" => "test.csv",
                "attachment_type" => "inline",
                "content_type" => "plain/text",
                "content_id" => null,
                "transfer_encoding" => null,
                "attachment_size" => 0,
                "created_at" => "2022-06-02T19:25:54.827Z",
                "updated_at" => "2022-06-02T19:25:54.827Z",
                "attachment_human_size" => "0 Bytes",
                "download_path" => "/api/accounts/3831/inboxes/4394/messages/457/attachments/67/download"
            ]
        ];
    }
}
