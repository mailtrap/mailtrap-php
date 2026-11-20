<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\General;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\General\SubscriptionTopic;
use Mailtrap\Exception\HttpClientException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers \Mailtrap\Api\General\SubscriptionTopic
 *
 * Class SubscriptionTopicTest
 */
class SubscriptionTopicTest extends MailtrapTestCase
{
    private const BASE_PATH = AbstractApi::DEFAULT_HOST . '/api/subscription_topics';

    private ?SubscriptionTopic $subscriptionTopic;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subscriptionTopic = $this->getMockBuilder(SubscriptionTopic::class)
            ->onlyMethods(['httpGet'])
            ->setConstructorArgs([$this->getConfigMock()])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->subscriptionTopic = null;
        parent::tearDown();
    }

    public function testGetSubscriptionTopics(): void
    {
        $this->subscriptionTopic->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_PATH, [])
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode([
                        'data' => [
                            $this->getExpectedSubscriptionTopicResponse(),
                            $this->getExpectedSubscriptionTopicResponse(id: 13, name: 'Product updates', domainId: 3938),
                        ],
                        'pagination' => $this->getExpectedPagination(),
                    ])
                )
            );

        $response = $this->subscriptionTopic->getSubscriptionTopics();
        $responseData = ResponseHelper::toArray($response);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('pagination', $responseData);
        $this->assertCount(2, $responseData['data']);
        $this->assertSame(12, $responseData['data'][0]['id']);
        // a topic without a domain covers every domain of the account
        $this->assertNull($responseData['data'][0]['domain_id']);
        $this->assertSame(3938, $responseData['data'][1]['domain_id']);
        $this->assertSame(1, $responseData['pagination']['token']);
        $this->assertSame(2, $responseData['pagination']['next_token']);
    }

    public function testGetSubscriptionTopicsWithPagination(): void
    {
        $this->subscriptionTopic->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_PATH, ['per_page' => 25, 'token' => 2])
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode([
                        'data' => [$this->getExpectedSubscriptionTopicResponse()],
                        'pagination' => $this->getExpectedPagination(),
                    ])
                )
            );

        $response = $this->subscriptionTopic->getSubscriptionTopics(perPage: 25, token: 2);
        $responseData = ResponseHelper::toArray($response);

        $this->assertCount(1, $responseData['data']);
    }

    public function testGetSubscriptionTopicsFailsForAccountWithoutTopics(): void
    {
        // without Subscription Topics the API answers 403 with an empty body
        $this->subscriptionTopic->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_PATH, [])
            ->willReturn(new Response(403));

        $this->expectException(HttpClientException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('Forbidden.');

        $this->subscriptionTopic->getSubscriptionTopics();
    }

    public function testGetSubscriptionTopic(): void
    {
        $subscriptionTopicId = 12;

        $this->subscriptionTopic->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_PATH . '/' . $subscriptionTopicId)
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(['data' => $this->getExpectedSubscriptionTopicResponse()])
                )
            );

        $response = $this->subscriptionTopic->getSubscriptionTopic($subscriptionTopicId);
        $responseData = ResponseHelper::toArray($response);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertSame($subscriptionTopicId, $responseData['data']['id']);
        $this->assertSame('Newsletter', $responseData['data']['name']);
        $this->assertNull($responseData['data']['deleted_at']);
    }

    public function testGetDeletedSubscriptionTopic(): void
    {
        $subscriptionTopicId = 14;

        $this->subscriptionTopic->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_PATH . '/' . $subscriptionTopicId)
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode([
                        'data' => $this->getExpectedSubscriptionTopicResponse(
                            id: $subscriptionTopicId,
                            name: 'Old offers',
                            deletedAt: '2026-06-01T12:00:00.000Z'
                        ),
                    ])
                )
            );

        $response = $this->subscriptionTopic->getSubscriptionTopic($subscriptionTopicId);
        $responseData = ResponseHelper::toArray($response);

        $this->assertSame($subscriptionTopicId, $responseData['data']['id']);
        $this->assertSame('2026-06-01T12:00:00.000Z', $responseData['data']['deleted_at']);
    }

    public function testGetSubscriptionTopicFailsWithNotFoundError(): void
    {
        $subscriptionTopicId = 999;

        $this->subscriptionTopic->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_PATH . '/' . $subscriptionTopicId)
            ->willReturn(
                new Response(404, ['Content-Type' => 'application/json'], json_encode(['error' => 'Not Found']))
            );

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('The requested entity has not been found. Errors: Not Found.');

        $this->subscriptionTopic->getSubscriptionTopic($subscriptionTopicId);
    }

    private function getExpectedSubscriptionTopicResponse(
        int $id = 12,
        string $name = 'Newsletter',
        ?int $domainId = null,
        ?string $deletedAt = null
    ): array {
        return [
            'id' => $id,
            'name' => $name,
            'description' => 'Monthly news and offers',
            'domain_id' => $domainId,
            'deleted_at' => $deletedAt,
            'created_at' => '2026-05-01T10:15:00.000Z',
            'updated_at' => '2026-05-02T09:00:00.000Z',
        ];
    }

    private function getExpectedPagination(): array
    {
        return [
            'token' => 1,
            'prev_token' => null,
            'next_token' => 2,
            'first_url' => 'https://mailtrap.io/api/subscription_topics?per_page=50&token=1',
            'prev_url' => null,
            'current_url' => 'https://mailtrap.io/api/subscription_topics?per_page=50&token=1',
            'next_url' => 'https://mailtrap.io/api/subscription_topics?per_page=50&token=2',
        ];
    }
}
