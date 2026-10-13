<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\General;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\General\MarketingStats;
use Mailtrap\DTO\Request\MarketingStats\MarketingStatsFilter;
use Mailtrap\DTO\Request\MarketingStats\MarketingStatsInterface;
use Mailtrap\Exception\HttpClientException;
use Mailtrap\Exception\InvalidArgumentException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers \Mailtrap\Api\General\MarketingStats
 *
 * Class MarketingStatsTest
 */
class MarketingStatsTest extends MailtrapTestCase
{
    private const BASE_PATH = AbstractApi::DEFAULT_HOST . '/api/marketing/stats';
    private const START_DATE = '2026-05-01';
    private const END_DATE = '2026-05-31';
    private const FAKE_SENDING_DOMAIN_ID = 3938;

    private ?MarketingStats $marketingStats;

    protected function setUp(): void
    {
        parent::setUp();
        $this->marketingStats = $this->getMockBuilder(MarketingStats::class)
            ->onlyMethods(['httpGet'])
            ->setConstructorArgs([$this->getConfigMock()])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->marketingStats = null;
        parent::tearDown();
    }

    public function testGetMarketingStats(): void
    {
        $this->marketingStats->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_PATH, ['start_date' => self::START_DATE, 'end_date' => self::END_DATE])
            ->willReturn($this->getStatsResponse());

        $response = $this->marketingStats->getMarketingStats(
            new MarketingStatsFilter(startDate: self::START_DATE, endDate: self::END_DATE)
        );
        $responseData = ResponseHelper::toArray($response);

        // stats are wrapped in a `data` envelope
        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals(1450, $responseData['data']['delivery_count']);
        $this->assertEquals(0.9667, $responseData['data']['delivery_rate']);
        $this->assertEquals(1500, $responseData['data']['sent_count']);
    }

    public function testGetMarketingStatsWithFilters(): void
    {
        $this->marketingStats->expects($this->once())
            ->method('httpGet')
            ->with(
                self::BASE_PATH,
                [
                    'start_date' => self::START_DATE,
                    'end_date' => self::END_DATE,
                    'sending_domain_ids' => [self::FAKE_SENDING_DOMAIN_ID],
                    'sources' => [MarketingStatsInterface::SOURCE_CAMPAIGNS],
                    'email_service_providers' => ['Google', 'Yahoo'],
                ]
            )
            ->willReturn($this->getStatsResponse());

        $response = $this->marketingStats->getMarketingStats(
            new MarketingStatsFilter(
                startDate: self::START_DATE,
                endDate: self::END_DATE,
                sendingDomainIds: [self::FAKE_SENDING_DOMAIN_ID],
                sources: [MarketingStatsInterface::SOURCE_CAMPAIGNS],
                emailServiceProviders: ['Google', 'Yahoo'],
            )
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testMarketingStatsFilterRejectsUnknownSource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"sources" must contain only "campaigns", "automations", "newsletters" given');

        new MarketingStatsFilter(startDate: self::START_DATE, endDate: self::END_DATE, sources: ['newsletters']);
    }

    public function testGetMarketingStatsInvalidDate(): void
    {
        $this->marketingStats->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_PATH, ['start_date' => 'yesterday', 'end_date' => self::END_DATE])
            ->willReturn(
                new Response(
                    400,
                    ['Content-Type' => 'application/json'],
                    json_encode(['errors' => "Invalid start_date: 'yesterday'"])
                )
            );

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage("Invalid start_date: 'yesterday'");

        $this->marketingStats->getMarketingStats(
            new MarketingStatsFilter(startDate: 'yesterday', endDate: self::END_DATE)
        );
    }

    public function testGetMarketingStatsForbidden(): void
    {
        $this->marketingStats->expects($this->once())
            ->method('httpGet')
            ->willReturn(
                new Response(403, ['Content-Type' => 'application/json'], json_encode(['errors' => 'Access forbidden']))
            );

        $this->expectException(HttpClientException::class);

        $this->marketingStats->getMarketingStats(
            new MarketingStatsFilter(startDate: self::START_DATE, endDate: self::END_DATE)
        );
    }

    private function getStatsResponse(): Response
    {
        return new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
                'data' => [
                    'delivery_count' => 1450,
                    'delivery_rate' => 0.9667,
                    'bounce_count' => 30,
                    'bounce_rate' => 0.02,
                    'open_count' => 820,
                    'open_rate' => 0.5655,
                    'click_count' => 310,
                    'click_rate' => 0.2138,
                    'spam_count' => 5,
                    'spam_rate' => 0.0033,
                    'unsubscription_count' => 12,
                    'unsubscription_rate' => 0.0083,
                    'sent_count' => 1500,
                ],
            ])
        );
    }
}
