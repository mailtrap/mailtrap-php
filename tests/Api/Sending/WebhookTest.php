<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\Sending;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Sending\Webhook;
use Mailtrap\DTO\Request\Webhook\CreateWebhook;
use Mailtrap\DTO\Request\Webhook\UpdateWebhook;
use Mailtrap\DTO\Request\Webhook\WebhookInterface;
use Mailtrap\Exception\HttpClientException;
use Mailtrap\Exception\RuntimeException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers Webhook
 *
 * Class WebhookTest
 */
class WebhookTest extends MailtrapTestCase
{
    private ?Webhook $webhook;

    protected function setUp(): void
    {
        parent::setUp();
        $this->webhook = $this->getMockBuilder(Webhook::class)
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
            webhookType: WebhookInterface::TYPE_EMAIL_SENDING,
            eventTypes: [WebhookInterface::EVENT_DELIVERY, WebhookInterface::EVENT_BOUNCE],
            payloadFormat: WebhookInterface::PAYLOAD_FORMAT_JSON,
            sendingStream: WebhookInterface::SENDING_STREAM_TRANSACTIONAL,
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
            webhookType: WebhookInterface::TYPE_EMAIL_SENDING,
            eventTypes: [WebhookInterface::EVENT_DELIVERY],
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
            'webhook_type' => WebhookInterface::TYPE_EMAIL_SENDING,
            'payload_format' => WebhookInterface::PAYLOAD_FORMAT_JSON,
            'sending_stream' => WebhookInterface::SENDING_STREAM_TRANSACTIONAL,
            'domain_id' => 435,
            'event_types' => [WebhookInterface::EVENT_DELIVERY, WebhookInterface::EVENT_BOUNCE],
        ];
    }
}
