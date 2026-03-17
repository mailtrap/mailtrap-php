<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\Sending;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Sending\Stats;
use Mailtrap\Exception\HttpClientException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers Stats
 *
 * Class StatsTest
 */
class StatsTest extends MailtrapTestCase
{
    private ?Stats $stats;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stats = $this->getMockBuilder(Stats::class)
            ->onlyMethods(['httpGet'])
            ->setConstructorArgs([$this->getConfigMock(), self::FAKE_ACCOUNT_ID])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->stats = null;

        parent::tearDown();
    }

    public function testGet(): void
    {
        $expectedData = $this->getSampleStatsData();

        $this->stats->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/stats',
                ['start_date' => '2026-01-01', 'end_date' => '2026-01-31']
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedData)));

        $response = $this->stats->get('2026-01-01', '2026-01-31');
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(150, $responseData['delivery_count']);
        $this->assertEquals(0.95, $responseData['delivery_rate']);
        $this->assertEquals(8, $responseData['bounce_count']);
        $this->assertEquals(120, $responseData['open_count']);
        $this->assertEquals(60, $responseData['click_count']);
        $this->assertEquals(2, $responseData['spam_count']);
    }

    public function testGetWithFilters(): void
    {
        $expectedData = $this->getSampleStatsData();

        $this->stats->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/stats',
                [
                    'start_date' => '2026-01-01',
                    'end_date' => '2026-01-31',
                    'sending_domain_ids' => [1, 2],
                    'sending_streams' => ['transactional'],
                    'categories' => ['Transactional'],
                    'email_service_providers' => ['Gmail'],
                ]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedData)));

        $response = $this->stats->get(
            '2026-01-01',
            '2026-01-31',
            [1, 2],
            ['transactional'],
            ['Transactional'],
            ['Gmail']
        );

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testGetForbidden(): void
    {
        $this->stats->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/stats',
                ['start_date' => '2026-01-01', 'end_date' => '2026-01-31']
            )
            ->willReturn(
                new Response(403, ['Content-Type' => 'application/json'], json_encode(['errors' => 'Access forbidden']))
            );

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage(
            'Forbidden. Make sure domain verification process is completed or check your permissions. Errors: Access forbidden.'
        );

        $this->stats->get('2026-01-01', '2026-01-31');
    }

    public function testByDomain(): void
    {
        $expectedData = $this->getSampleGroupedByDomainsData();

        $this->stats->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/stats/domains',
                ['start_date' => '2026-01-01', 'end_date' => '2026-01-31']
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedData)));

        $response = $this->stats->byDomain('2026-01-01', '2026-01-31');
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertCount(2, $responseData);
        $this->assertEquals(1, $responseData[0]['sending_domain_id']);
        $this->assertEquals(100, $responseData[0]['stats']['delivery_count']);
        $this->assertEquals(2, $responseData[1]['sending_domain_id']);
        $this->assertEquals(50, $responseData[1]['stats']['delivery_count']);
    }

    public function testByCategory(): void
    {
        $expectedData = [
            [
                'category' => 'Transactional',
                'stats' => [
                    'delivery_count' => 100, 'delivery_rate' => 0.97,
                    'bounce_count' => 3, 'bounce_rate' => 0.03,
                    'open_count' => 85, 'open_rate' => 0.85,
                    'click_count' => 45, 'click_rate' => 0.53,
                    'spam_count' => 0, 'spam_rate' => 0.0,
                ],
            ],
        ];

        $this->stats->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/stats/categories',
                ['start_date' => '2026-01-01', 'end_date' => '2026-01-31']
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedData)));

        $response = $this->stats->byCategory('2026-01-01', '2026-01-31');
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Transactional', $responseData[0]['category']);
        $this->assertEquals(100, $responseData[0]['stats']['delivery_count']);
    }

    public function testByEmailServiceProvider(): void
    {
        $expectedData = [
            [
                'email_service_provider' => 'Gmail',
                'stats' => [
                    'delivery_count' => 80, 'delivery_rate' => 0.97,
                    'bounce_count' => 2, 'bounce_rate' => 0.03,
                    'open_count' => 70, 'open_rate' => 0.88,
                    'click_count' => 35, 'click_rate' => 0.5,
                    'spam_count' => 1, 'spam_rate' => 0.013,
                ],
            ],
        ];

        $this->stats->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/stats/email_service_providers',
                ['start_date' => '2026-01-01', 'end_date' => '2026-01-31']
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedData)));

        $response = $this->stats->byEmailServiceProvider('2026-01-01', '2026-01-31');
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Gmail', $responseData[0]['email_service_provider']);
        $this->assertEquals(80, $responseData[0]['stats']['delivery_count']);
    }

    public function testByDate(): void
    {
        $expectedData = [
            [
                'date' => '2026-01-01',
                'stats' => [
                    'delivery_count' => 5, 'delivery_rate' => 1.0,
                    'bounce_count' => 0, 'bounce_rate' => 0.0,
                    'open_count' => 4, 'open_rate' => 0.8,
                    'click_count' => 2, 'click_rate' => 0.5,
                    'spam_count' => 0, 'spam_rate' => 0.0,
                ],
            ],
        ];

        $this->stats->expects($this->once())
            ->method('httpGet')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/stats/date',
                ['start_date' => '2026-01-01', 'end_date' => '2026-01-31']
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedData)));

        $response = $this->stats->byDate('2026-01-01', '2026-01-31');
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('2026-01-01', $responseData[0]['date']);
        $this->assertEquals(5, $responseData[0]['stats']['delivery_count']);
    }

    private function getSampleStatsData(): array
    {
        return [
            'delivery_count' => 150, 'delivery_rate' => 0.95,
            'bounce_count' => 8, 'bounce_rate' => 0.05,
            'open_count' => 120, 'open_rate' => 0.8,
            'click_count' => 60, 'click_rate' => 0.5,
            'spam_count' => 2, 'spam_rate' => 0.013,
        ];
    }

    private function getSampleGroupedByDomainsData(): array
    {
        return [
            [
                'sending_domain_id' => 1,
                'stats' => [
                    'delivery_count' => 100, 'delivery_rate' => 0.96,
                    'bounce_count' => 4, 'bounce_rate' => 0.04,
                    'open_count' => 80, 'open_rate' => 0.8,
                    'click_count' => 40, 'click_rate' => 0.5,
                    'spam_count' => 1, 'spam_rate' => 0.01,
                ],
            ],
            [
                'sending_domain_id' => 2,
                'stats' => [
                    'delivery_count' => 50, 'delivery_rate' => 0.93,
                    'bounce_count' => 4, 'bounce_rate' => 0.07,
                    'open_count' => 40, 'open_rate' => 0.8,
                    'click_count' => 20, 'click_rate' => 0.5,
                    'spam_count' => 1, 'spam_rate' => 0.02,
                ],
            ],
        ];
    }
}
