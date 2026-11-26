<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\Sending;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Sending\Suppression;
use Mailtrap\DTO\Request\Suppression\CreateSuppression;
use Mailtrap\DTO\Request\Suppression\Suppression as SuppressionDto;
use Mailtrap\DTO\Request\Suppression\SuppressionsFilter;
use Mailtrap\Exception\HttpClientException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers Suppression
 *
 * Class SuppressionTest
 */
class SuppressionTest extends MailtrapTestCase
{
    private const DOMAIN_ID = 12345;

    private ?Suppression $suppression;

    protected function setUp(): void
    {
        parent::setUp();
        $this->suppression = $this->getMockBuilder(Suppression::class)
            ->onlyMethods(['httpGet', 'httpPost', 'httpDelete'])
            ->setConstructorArgs([$this->getConfigMock(), self::FAKE_ACCOUNT_ID])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->suppression = null;
        parent::tearDown();
    }

    public function testGetSuppressionsWithoutEmail(): void
    {
        $expectedResponseBody = $this->getExpectedResponse('john_6832ebeadcf31@example.com');

        $this->suppression->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/suppressions')
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], $expectedResponseBody));

        $response = $this->suppression->getSuppressions();
        $responseData = ResponseHelper::toArray($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('id', $responseData[0]);
    }

    public function testGetSuppressionsWithEmail(): void
    {
        $email = 'test@mail.com';
        $expectedResponseBody = $this->getExpectedResponse($email);

        $this->suppression->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/suppressions', ['email' => $email])
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], $expectedResponseBody));

        $response = $this->suppression->getSuppressions($email);
        $responseData = ResponseHelper::toArray($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('email', $responseData[0]);
        $this->assertSame($email, $responseData[0]['email']);
    }

    public function testDeleteSuppression(): void
    {
        $suppressionId = '25594eef-87e0-49c7-a647-cc316f9fdb42';

        $this->suppression->expects($this->once())
            ->method('httpDelete')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/suppressions/' . $suppressionId)
            ->willReturn(new Response(200));

        $response = $this->suppression->deleteSuppression($suppressionId);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testDeleteSuppressionNotFound(): void
    {
        $suppressionId = 'non-existent-id';
        $expectedErrorResponse = [
            'error' => 'Suppression not found',
        ];

        $this->suppression->expects($this->once())
            ->method('httpDelete')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/suppressions/' . $suppressionId)
            ->willReturn(
                new Response(404, ['Content-Type' => 'application/json'], json_encode($expectedErrorResponse))
            );

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('The requested entity has not been found. Errors: Suppression not found.');

        $this->suppression->deleteSuppression($suppressionId);
    }

    public function testGetSuppressionsWithFilter(): void
    {
        $this->suppression->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/suppressions',
                [
                    'email' => 'john@example.com',
                    'start_time' => '2025-01-01T00:00:00Z',
                    'end_time' => '2025-12-31T23:59:59Z',
                    'last_id' => '25594eef-87e0-49c7-a647-cc316f9fdb42',
                    'include_topic_unsubscriptions' => 'true',
                ]
            )
            ->willReturn(
                new Response(200, ['Content-Type' => 'application/json'], $this->getExpectedResponse('john@example.com'))
            );

        $response = $this->suppression->getSuppressions(
            new SuppressionsFilter(
                email: 'john@example.com',
                startTime: '2025-01-01T00:00:00Z',
                endTime: '2025-12-31T23:59:59Z',
                lastId: '25594eef-87e0-49c7-a647-cc316f9fdb42',
                includeTopicUnsubscriptions: true
            )
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testGetSuppressionsWithTopicUnsubscriptions(): void
    {
        $filter = new SuppressionsFilter(includeTopicUnsubscriptions: true);

        // the API opts in only on the literal string "true", not on 1
        $this->assertSame('include_topic_unsubscriptions=true', http_build_query($filter->toArray()));

        $this->suppression->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/suppressions',
                ['include_topic_unsubscriptions' => 'true']
            )
            ->willReturn(
                new Response(200, ['Content-Type' => 'application/json'], $this->getExpectedTopicUnsubscriptionResponse())
            );

        $responseData = ResponseHelper::toArray($this->suppression->getSuppressions($filter));

        $this->assertSame(SuppressionDto::TYPE_TOPIC_UNSUBSCRIPTION, $responseData[1]['type']);
        $this->assertSame(17, $responseData[1]['subscription_topic_id']);
        $this->assertSame('example.com', $responseData[1]['domain_name']);
        $this->assertNull($responseData[0]['subscription_topic_id']);
    }

    public function testGetSuppressionsWithoutTopicUnsubscriptionsSendsNothing(): void
    {
        $this->assertSame([], (new SuppressionsFilter())->toArray());
        $this->assertSame([], (new SuppressionsFilter(includeTopicUnsubscriptions: false))->toArray());
    }

    public function testGetSuppressionsWithFilterOmitsUnsetFields(): void
    {
        $this->suppression->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/suppressions',
                ['email' => 'john@example.com']
            )
            ->willReturn(
                new Response(200, ['Content-Type' => 'application/json'], $this->getExpectedResponse('john@example.com'))
            );

        $response = $this->suppression->getSuppressions(new SuppressionsFilter(email: 'john@example.com'));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testCreateSuppression(): void
    {
        $this->suppression->expects($this->once())
            ->method('httpPost')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/suppressions',
                [],
                [
                    'email' => 'recipient@example.com',
                    'domain_id' => self::DOMAIN_ID,
                    'sending_stream' => SuppressionDto::SENDING_STREAM_TRANSACTIONAL,
                ]
            )
            ->willReturn(
                new Response(201, ['Content-Type' => 'application/json'], $this->getExpectedCreateResponse())
            );

        $response = $this->suppression->createSuppression(
            new CreateSuppression(
                email: 'recipient@example.com',
                domainId: self::DOMAIN_ID,
                sendingStream: SuppressionDto::SENDING_STREAM_TRANSACTIONAL
            )
        );
        $responseData = ResponseHelper::toArray($response);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertArrayHasKey('data', $responseData);
        $this->assertSame('recipient@example.com', $responseData['data']['email']);
    }

    public function testCreateSuppressionWithExplicitType(): void
    {
        $this->suppression->expects($this->once())
            ->method('httpPost')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/suppressions',
                [],
                [
                    'email' => 'recipient@example.com',
                    'domain_id' => self::DOMAIN_ID,
                    'sending_stream' => SuppressionDto::SENDING_STREAM_BULK,
                    'type' => SuppressionDto::TYPE_SPAM_COMPLAINT,
                ]
            )
            ->willReturn(
                new Response(201, ['Content-Type' => 'application/json'], $this->getExpectedCreateResponse())
            );

        $response = $this->suppression->createSuppression(
            new CreateSuppression(
                email: 'recipient@example.com',
                domainId: self::DOMAIN_ID,
                sendingStream: SuppressionDto::SENDING_STREAM_BULK,
                type: SuppressionDto::TYPE_SPAM_COMPLAINT
            )
        );

        $this->assertSame(201, $response->getStatusCode());
    }

    private function getExpectedCreateResponse(): string
    {
        return json_encode([
            'data' => [
                'id' => '25594eef-87e0-49c7-a647-cc316f9fdb42',
                'type' => 'manual import',
                'created_at' => '2025-05-25T10:07:49Z',
                'email' => 'recipient@example.com',
                'sending_stream' => 'transactional',
                'domain_name' => 'example.com',
            ]
        ]);
    }

    private function getExpectedTopicUnsubscriptionResponse(): string
    {
        return json_encode([
            [
                'id' => '25594eef-87e0-49c7-a647-cc316f9fdb42',
                'type' => 'unsubscription',
                'created_at' => '2025-05-25T10:07:49Z',
                'email' => 'john@example.com',
                'sending_stream' => 'bulk',
                'domain_name' => 'example.com',
                'subscription_topic_id' => null,
            ],
            [
                'id' => '7a1b4f0e-2c3d-4e5f-8a9b-0c1d2e3f4a5b',
                'type' => 'topic unsubscription',
                'created_at' => '2026-06-01T12:00:00Z',
                'email' => 'jane@example.com',
                'sending_stream' => 'bulk',
                'domain_name' => 'example.com',
                'subscription_topic_id' => 17,
            ],
        ]);
    }

    private function getExpectedResponse(string $email): string
    {
        return json_encode([
            [
               "id" => "25594eef-87e0-49c7-a647-cc316f9fdb42",
               "type" => "unsubscription",
               "created_at" => "2025-05-25T10:07:49Z",
               "email" => $email,
               "sending_stream" => "bulk",
               "domain_name" => "",
               "message_bounce_category" => "",
               "message_category" => "",
               "message_client_ip" => "",
               "message_created_at" => "",
               "message_esp_response" => "",
               "message_esp_server_type" => "",
               "message_outgoing_ip" => "",
               "message_recipient_mx_name" => "",
               "message_sender_email" => "",
               "message_subject" => "",
            ],
        ]);
    }
}
