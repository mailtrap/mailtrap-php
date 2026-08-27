<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\Sending;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Sending\TrackingOptOut;
use Mailtrap\DTO\Request\TrackingOptOut\CreateTrackingOptOut;
use Mailtrap\DTO\Request\TrackingOptOut\TrackingOptOutsFilter;
use Mailtrap\Exception\HttpClientException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers TrackingOptOut
 *
 * Class TrackingOptOutTest
 */
class TrackingOptOutTest extends MailtrapTestCase
{
    private const DOMAIN_ID = 12345;
    private const OPT_OUT_ID = '64d71bf3-1276-417b-86e1-8e66f138acfe';

    private ?TrackingOptOut $trackingOptOut;

    protected function setUp(): void
    {
        parent::setUp();
        $this->trackingOptOut = $this->getMockBuilder(TrackingOptOut::class)
            ->onlyMethods(['httpGet', 'httpPost', 'httpDelete'])
            ->setConstructorArgs([$this->getConfigMock()])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->trackingOptOut = null;
        parent::tearDown();
    }

    public function testGetTrackingOptOuts(): void
    {
        $this->trackingOptOut->expects($this->once())
            ->method('httpGet')
            ->with($this->getExpectedPath(), [])
            ->willReturn(
                new Response(200, ['Content-Type' => 'application/json'], $this->getExpectedListResponse())
            );

        $response = $this->trackingOptOut->getTrackingOptOuts();
        $responseData = ResponseHelper::toArray($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('last_id', $responseData);
        $this->assertSame('tracked@example.com', $responseData['data'][0]['email']);
    }

    public function testGetTrackingOptOutsWithFilter(): void
    {
        $this->trackingOptOut->expects($this->once())
            ->method('httpGet')
            ->with(
                $this->getExpectedPath(),
                [
                    'email' => 'tracked@example.com',
                    'start_time' => '2025-01-01T00:00:00Z',
                    'end_time' => '2025-12-31T23:59:59Z',
                    'last_id' => self::OPT_OUT_ID,
                ]
            )
            ->willReturn(
                new Response(200, ['Content-Type' => 'application/json'], $this->getExpectedListResponse())
            );

        $response = $this->trackingOptOut->getTrackingOptOuts(
            new TrackingOptOutsFilter(
                email: 'tracked@example.com',
                startTime: '2025-01-01T00:00:00Z',
                endTime: '2025-12-31T23:59:59Z',
                lastId: self::OPT_OUT_ID
            )
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testGetTrackingOptOutsOmitsUnsetFilters(): void
    {
        $this->trackingOptOut->expects($this->once())
            ->method('httpGet')
            ->with($this->getExpectedPath(), ['email' => 'tracked@example.com'])
            ->willReturn(
                new Response(200, ['Content-Type' => 'application/json'], $this->getExpectedListResponse())
            );

        $response = $this->trackingOptOut->getTrackingOptOuts(
            new TrackingOptOutsFilter(email: 'tracked@example.com')
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testCreateTrackingOptOut(): void
    {
        $this->trackingOptOut->expects($this->once())
            ->method('httpPost')
            ->with(
                $this->getExpectedPath(),
                [],
                ['email' => 'tracked@example.com', 'domain_id' => self::DOMAIN_ID]
            )
            ->willReturn(
                new Response(201, ['Content-Type' => 'application/json'], $this->getExpectedCreateResponse())
            );

        $response = $this->trackingOptOut->createTrackingOptOut(
            new CreateTrackingOptOut(email: 'tracked@example.com', domainId: self::DOMAIN_ID)
        );
        $responseData = ResponseHelper::toArray($response);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertArrayHasKey('data', $responseData);
        $this->assertSame(self::OPT_OUT_ID, $responseData['data']['id']);
    }

    public function testCreateTrackingOptOutInvalid(): void
    {
        $this->trackingOptOut->expects($this->once())
            ->method('httpPost')
            ->willReturn(
                new Response(
                    422,
                    ['Content-Type' => 'application/json'],
                    json_encode(['errors' => 'Email is invalid'])
                )
            );

        $this->expectException(HttpClientException::class);

        $this->trackingOptOut->createTrackingOptOut(
            new CreateTrackingOptOut(email: 'not-an-email', domainId: self::DOMAIN_ID)
        );
    }

    public function testDeleteTrackingOptOut(): void
    {
        $this->trackingOptOut->expects($this->once())
            ->method('httpDelete')
            ->with($this->getExpectedPath() . '/' . self::OPT_OUT_ID)
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode($this->getOptOutPayload())
                )
            );

        $response = $this->trackingOptOut->deleteTrackingOptOut(self::OPT_OUT_ID);
        $responseData = ResponseHelper::toArray($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(self::OPT_OUT_ID, $responseData['id']);
    }

    public function testDeleteTrackingOptOutNotFound(): void
    {
        $this->trackingOptOut->expects($this->once())
            ->method('httpDelete')
            ->with($this->getExpectedPath() . '/missing')
            ->willReturn(
                new Response(
                    404,
                    ['Content-Type' => 'application/json'],
                    json_encode(['errors' => 'Tracking opt-out not found'])
                )
            );

        $this->expectException(HttpClientException::class);

        $this->trackingOptOut->deleteTrackingOptOut('missing');
    }

    private function getExpectedPath(): string
    {
        return AbstractApi::DEFAULT_HOST . '/api/tracking_opt_outs';
    }

    private function getOptOutPayload(): array
    {
        return [
            'id' => self::OPT_OUT_ID,
            'email' => 'tracked@example.com',
            'created_at' => '2025-01-15T10:30:00Z',
            'domain_name' => 'example.com',
        ];
    }

    private function getExpectedListResponse(): string
    {
        return json_encode(['data' => [$this->getOptOutPayload()], 'last_id' => null]);
    }

    private function getExpectedCreateResponse(): string
    {
        return json_encode(['data' => $this->getOptOutPayload()]);
    }
}
