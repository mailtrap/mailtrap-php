<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\Sending;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Sending\Webhook as WebhookApi;
use Mailtrap\DTO\Request\Webhook\CreateWebhook;
use Mailtrap\DTO\Request\Webhook\UpdateWebhook;
use Mailtrap\DTO\Request\Webhook\Webhook;
use Mailtrap\Exception\HttpClientException;
use Mailtrap\Exception\InvalidArgumentException;
use Mailtrap\Exception\RuntimeException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers WebhookApi
 *
 * Class WebhookTest
 */
class WebhookTest extends MailtrapTestCase
{
    private ?WebhookApi $webhook;

    protected function setUp(): void
    {
        parent::setUp();
        $this->webhook = $this->getMockBuilder(WebhookApi::class)
            ->onlyMethods(['httpGet', 'httpPost', 'httpPatch', 'httpDelete'])
            ->setConstructorArgs([$this->getConfigMock(), self::FAKE_ACCOUNT_ID])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->webhook = null;
        parent::tearDown();
    }

    public function testGetWebhooks(): void
    {
        $this->webhook->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/webhooks')
            ->willReturn(
                new Response(200, ['Content-Type' => 'application/json'], json_encode(['data' => [$this->getExpectedWebhookResponse()]]))
            );

        $response = $this->webhook->getWebhooks();
        $responseData = ResponseHelper::toArray($response);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertCount(1, $responseData['data']);
        $this->assertArrayHasKey('id', $responseData['data'][0]);
        $this->assertArrayHasKey('url', $responseData['data'][0]);
    }

    public function testGetWebhookById(): void
    {
        $webhookId = 1;

        $this->webhook->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/webhooks/' . $webhookId)
            ->willReturn(
                new Response(200, ['Content-Type' => 'application/json'], json_encode(['data' => $this->getExpectedWebhookResponse()]))
            );

        $response = $this->webhook->getWebhook($webhookId);
        $responseData = ResponseHelper::toArray($response);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals($webhookId, $responseData['data']['id']);
    }

    public function testGetWebhookFailsWithNotFound(): void
    {
        $webhookId = 999;

        $this->webhook->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/webhooks/' . $webhookId)
            ->willReturn(
                new Response(404, ['Content-Type' => 'application/json'], json_encode(['error' => 'Not Found']))
            );

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('The requested entity has not been found. Errors: Not Found.');

        $this->webhook->getWebhook($webhookId);
    }

    public function testCreateWebhook(): void
    {
        $createDto = new CreateWebhook(
            url: 'https://example.com/mailtrap/webhooks',
            webhookType: Webhook::TYPE_EMAIL_SENDING,
            eventTypes: [Webhook::EVENT_DELIVERY, Webhook::EVENT_BOUNCE],
            payloadFormat: Webhook::PAYLOAD_FORMAT_JSON,
            sendingStream: Webhook::SENDING_STREAM_TRANSACTIONAL,
            domainId: 435,
        );

        $this->webhook->expects($this->once())
            ->method('httpPost')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/webhooks',
                [],
                ['webhook' => $createDto->toArray()]
            )
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(['data' => $this->getExpectedWebhookResponse() + ['signing_secret' => 'shhh-very-secret']])
                )
            );

        $response = $this->webhook->createWebhook($createDto);
        $responseData = ResponseHelper::toArray($response);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('id', $responseData['data']);
        $this->assertArrayHasKey('signing_secret', $responseData['data']);
    }

    public function testCreateWebhookFailsWithValidationError(): void
    {
        $invalidDto = new CreateWebhook(
            url: 'not-a-url',
            webhookType: Webhook::TYPE_EMAIL_SENDING,
            eventTypes: [Webhook::EVENT_DELIVERY],
            sendingStream: Webhook::SENDING_STREAM_TRANSACTIONAL,
        );

        $this->webhook->expects($this->once())
            ->method('httpPost')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/webhooks',
                [],
                ['webhook' => $invalidDto->toArray()]
            )
            ->willReturn(
                new Response(422, ['Content-Type' => 'application/json'], json_encode(['errors' => ['url' => ['is invalid']]]))
            );

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('url -> is invalid');

        $this->webhook->createWebhook($invalidDto);
    }

    public function testCreateWebhookRejectsUnknownWebhookType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"webhookType" must be one of "email_sending" or "audit_log", "spam" given');

        new CreateWebhook(
            url: 'https://example.com/mailtrap/webhooks',
            webhookType: 'spam',
        );
    }

    public function testCreateWebhookRejectsEmailSendingWithoutEventTypes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"eventTypes" is required for email_sending webhooks');

        new CreateWebhook(
            url: 'https://example.com/mailtrap/webhooks',
            webhookType: Webhook::TYPE_EMAIL_SENDING,
            sendingStream: Webhook::SENDING_STREAM_TRANSACTIONAL,
        );
    }

    public function testCreateWebhookRejectsEmailSendingWithoutSendingStream(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"sendingStream" is required for email_sending webhooks');

        new CreateWebhook(
            url: 'https://example.com/mailtrap/webhooks',
            webhookType: Webhook::TYPE_EMAIL_SENDING,
            eventTypes: [Webhook::EVENT_DELIVERY],
        );
    }

    public function testUpdateWebhook(): void
    {
        $webhookId = 1;
        $updateDto = new UpdateWebhook(
            url: 'https://example.com/new-endpoint',
            active: false,
        );

        $this->webhook->expects($this->once())
            ->method('httpPatch')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/webhooks/' . $webhookId,
                [],
                ['webhook' => $updateDto->toArray()]
            )
            ->willReturn(
                new Response(200, ['Content-Type' => 'application/json'], json_encode(['data' => $this->getExpectedWebhookResponse()]))
            );

        $response = $this->webhook->updateWebhook($webhookId, $updateDto);
        $responseData = ResponseHelper::toArray($response);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals($webhookId, $responseData['data']['id']);
    }

    public function testUpdateWebhookFailsWithEmptyPayload(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('At least one updatable field must be provided to update a webhook');

        $this->webhook->updateWebhook(1, new UpdateWebhook());
    }

    public function testDeleteWebhook(): void
    {
        $webhookId = 1;

        $this->webhook->expects($this->once())
            ->method('httpDelete')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/webhooks/' . $webhookId)
            ->willReturn(
                new Response(200, ['Content-Type' => 'application/json'], json_encode(['data' => $this->getExpectedWebhookResponse()]))
            );

        $response = $this->webhook->deleteWebhook($webhookId);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('data', ResponseHelper::toArray($response));
    }

    private function getExpectedWebhookResponse(): array
    {
        return [
            'id' => 1,
            'url' => 'https://example.com/mailtrap/webhooks',
            'active' => true,
            'webhook_type' => Webhook::TYPE_EMAIL_SENDING,
            'payload_format' => Webhook::PAYLOAD_FORMAT_JSON,
            'sending_stream' => Webhook::SENDING_STREAM_TRANSACTIONAL,
            'domain_id' => 435,
            'event_types' => [Webhook::EVENT_DELIVERY, Webhook::EVENT_BOUNCE],
        ];
    }
}
