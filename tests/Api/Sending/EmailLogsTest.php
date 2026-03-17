<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\Sending;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Sending\EmailLogs;
use Mailtrap\DTO\Request\EmailLogs\EmailLogsFilterOperator;
use Mailtrap\DTO\Request\EmailLogs\EmailLogsFilterValue;
use Mailtrap\DTO\Request\EmailLogs\EmailLogsListFilters;
use Mailtrap\DTO\Request\EmailLogs\FilterCriterion;
use Mailtrap\Exception\InvalidArgumentException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers \Mailtrap\Api\Sending\EmailLogs
 */
class EmailLogsTest extends MailtrapTestCase
{
    private ?EmailLogs $emailLogs = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emailLogs = $this->getMockBuilder(EmailLogs::class)
            ->onlyMethods(['httpGet'])
            ->setConstructorArgs([$this->getConfigMock(), self::FAKE_ACCOUNT_ID])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->emailLogs = null;
        parent::tearDown();
    }

    public function testGetListWithoutFilters(): void
    {
        $basePath = AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/email_logs';
        $body = $this->getListResponseBody();

        $this->emailLogs->expects($this->once())
            ->method('httpGet')
            ->with($basePath, [])
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($body)));

        $response = $this->emailLogs->getList();
        $data = ResponseHelper::toArray($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('messages', $data);
        $this->assertArrayHasKey('total_count', $data);
        $this->assertArrayHasKey('next_page_cursor', $data);
        $this->assertSame(150, $data['total_count']);
        $this->assertCount(2, $data['messages']);
        $this->assertSame('a1b2c3d4-e5f6-7890-abcd-ef1234567890', $data['messages'][0]['message_id']);
    }

    public function testGetListWithFiltersAndSearchAfter(): void
    {
        $basePath = AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/email_logs';
        $filters = [
            'sent_after' => '2025-01-01T00:00:00Z',
            'sent_before' => '2025-01-31T23:59:59Z',
            'to' => ['operator' => 'ci_equal', 'value' => 'recipient@example.com'],
            'sending_domain_id' => ['operator' => 'equal', 'value' => [3938, 3939]],
        ];
        $searchAfter = 'b2c3d4e5-f6a7-8901-bcde-f12345678901';
        $params = [
            'search_after' => $searchAfter,
            'filters' => $filters,
        ];
        $body = $this->getListResponseBody();

        $this->emailLogs->expects($this->once())
            ->method('httpGet')
            ->with($basePath, $params)
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($body)));

        $response = $this->emailLogs->getList($filters, $searchAfter);
        $data = ResponseHelper::toArray($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('messages', $data);
    }

    public function testGetListWithEmailLogsListFiltersModel(): void
    {
        $basePath = AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/email_logs';
        $filters = EmailLogsListFilters::create(
            '2025-01-01T00:00:00Z',
            '2025-01-31T23:59:59Z',
            [
                'to' => FilterCriterion::withValue(EmailLogsFilterOperator::CI_EQUAL, 'recipient@example.com'),
                'subject' => FilterCriterion::withoutValue(EmailLogsFilterOperator::NOT_EMPTY),
                'category' => FilterCriterion::withValue(EmailLogsFilterOperator::EQUAL, ['Welcome Email', 'Order Confirmation']),
            ]
        );
        $expectedParams = [
            'filters' => $filters->toArray(),
        ];
        $body = $this->getListResponseBody();

        $this->emailLogs->expects($this->once())
            ->method('httpGet')
            ->with($basePath, $expectedParams)
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($body)));

        $response = $this->emailLogs->getList($filters);
        $data = ResponseHelper::toArray($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('messages', $data);
        $this->assertSame('2025-01-01T00:00:00Z', $expectedParams['filters']['sent_after']);
        $this->assertSame(EmailLogsFilterOperator::NOT_EMPTY, $expectedParams['filters']['subject']['operator']);
        $this->assertArrayNotHasKey('value', $expectedParams['filters']['subject']);
    }

    public function testGetListWithOperatorAndValueConstants(): void
    {
        $basePath = AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/email_logs';
        $filters = EmailLogsListFilters::create(
            null,
            null,
            [
                'status' => FilterCriterion::withValue(EmailLogsFilterOperator::EQUAL, EmailLogsFilterValue::STATUS_DELIVERED),
                'events' => FilterCriterion::withValue(EmailLogsFilterOperator::INCLUDE_EVENT, EmailLogsFilterValue::EVENT_OPEN),
                'sending_stream' => FilterCriterion::withValue(EmailLogsFilterOperator::EQUAL, EmailLogsFilterValue::STREAM_TRANSACTIONAL),
            ]
        );
        $expectedParams = ['filters' => $filters->toArray()];
        $body = $this->getListResponseBody();

        $this->emailLogs->expects($this->once())
            ->method('httpGet')
            ->with($basePath, $expectedParams)
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($body)));

        $response = $this->emailLogs->getList($filters);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(EmailLogsFilterValue::STATUS_DELIVERED, $expectedParams['filters']['status']['value']);
        $this->assertSame(EmailLogsFilterValue::EVENT_OPEN, $expectedParams['filters']['events']['value']);
        $this->assertSame(EmailLogsFilterValue::STREAM_TRANSACTIONAL, $expectedParams['filters']['sending_stream']['value']);
    }

    public function testGetMessage(): void
    {
        $messageId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $basePath = AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/email_logs/' . $messageId;
        $body = $this->getMessageResponseBody();

        $this->emailLogs->expects($this->once())
            ->method('httpGet')
            ->with($basePath, [])
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($body)));

        $response = $this->emailLogs->getMessage($messageId);
        $data = ResponseHelper::toArray($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($messageId, $data['message_id']);
        $this->assertSame('delivered', $data['status']);
        $this->assertArrayHasKey('events', $data);
    }

    public function testGetMessageThrowsWhenSendingMessageIdIsEmpty(): void
    {
        $emailLogs = new EmailLogs($this->getConfigMock(), self::FAKE_ACCOUNT_ID);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('sending_message_id must not be empty.');

        $emailLogs->getMessage('');
    }

    public function testGetMessageThrowsWhenSendingMessageIdIsWhitespaceOnly(): void
    {
        $emailLogs = new EmailLogs($this->getConfigMock(), self::FAKE_ACCOUNT_ID);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('sending_message_id must not be empty.');

        $emailLogs->getMessage('   ');
    }

    private function getListResponseBody(): array
    {
        return [
            'messages' => [
                [
                    'message_id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                    'status' => 'delivered',
                    'subject' => 'Welcome to our service',
                    'from' => 'sender@example.com',
                    'to' => 'recipient@example.com',
                    'sent_at' => '2025-01-15T10:30:00Z',
                    'client_ip' => '203.0.113.42',
                    'category' => 'Welcome Email',
                    'custom_variables' => [],
                    'sending_stream' => 'transactional',
                    'sending_domain_id' => 3938,
                    'template_id' => 100,
                    'template_variables' => [],
                    'opens_count' => 2,
                    'clicks_count' => 1,
                ],
                [
                    'message_id' => 'b2c3d4e5-f6a7-8901-bcde-f12345678901',
                    'status' => 'delivered',
                    'subject' => 'Your order confirmation',
                    'from' => 'orders@example.com',
                    'to' => 'customer@example.com',
                    'sent_at' => '2025-01-15T11:00:00Z',
                    'client_ip' => null,
                    'category' => 'Order Confirmation',
                    'custom_variables' => ['order_id' => '12345'],
                    'sending_stream' => 'transactional',
                    'sending_domain_id' => 3938,
                    'template_id' => null,
                    'template_variables' => [],
                    'opens_count' => 0,
                    'clicks_count' => 0,
                ],
            ],
            'total_count' => 150,
            'next_page_cursor' => 'b2c3d4e5-f6a7-8901-bcde-f12345678901',
        ];
    }

    private function getMessageResponseBody(): array
    {
        return [
            'message_id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            'status' => 'delivered',
            'subject' => 'Welcome to our service',
            'from' => 'sender@example.com',
            'to' => 'recipient@example.com',
            'sent_at' => '2025-01-15T10:30:00Z',
            'client_ip' => '203.0.113.42',
            'category' => 'Welcome Email',
            'custom_variables' => [],
            'sending_stream' => 'transactional',
            'sending_domain_id' => 3938,
            'template_id' => 100,
            'template_variables' => [],
            'opens_count' => 2,
            'clicks_count' => 1,
            'raw_message_url' => 'https://storage.example.com/signed/eml/a1b2c3d4-e5f6-7890-abcd-ef1234567890?token=...',
            'events' => [
                [
                    'event_type' => 'click',
                    'created_at' => '2025-01-15T10:35:00Z',
                    'details' => [
                        'click_url' => 'https://example.com/track/click/abc123',
                        'web_ip_address' => '198.51.100.50',
                    ],
                ],
            ],
        ];
    }
}
