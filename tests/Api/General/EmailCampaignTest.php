<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\General;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\General\EmailCampaign;
use Mailtrap\DTO\Request\EmailCampaign\CreateEmailCampaign;
use Mailtrap\DTO\Request\EmailCampaign\EmailCampaignInterface;
use Mailtrap\DTO\Request\EmailCampaign\ReplyTo;
use Mailtrap\DTO\Request\EmailCampaign\TemplateAttributes;
use Mailtrap\DTO\Request\EmailCampaign\UpdateEmailCampaign;
use Mailtrap\Exception\HttpClientException;
use Mailtrap\Exception\RuntimeException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers \Mailtrap\Api\General\EmailCampaign
 *
 * Class EmailCampaignTest
 */
class EmailCampaignTest extends MailtrapTestCase
{
    private const BASE_PATH = AbstractApi::DEFAULT_HOST . '/api/email_campaigns';
    private const FAKE_DOMAIN_ID = 4321;

    private ?EmailCampaign $emailCampaign;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emailCampaign = $this->getMockBuilder(EmailCampaign::class)
            ->onlyMethods(['httpGet', 'httpPost', 'httpPatch', 'httpDelete'])
            ->setConstructorArgs([$this->getConfigMock(), self::FAKE_ACCOUNT_ID])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->emailCampaign = null;
        parent::tearDown();
    }

    public function testGetEmailCampaigns(): void
    {
        $this->emailCampaign->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_PATH, [])
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode([
                        'data' => [$this->getExpectedEmailCampaignResponse()],
                        'pagination' => $this->getExpectedPagination(),
                    ])
                )
            );

        $response = $this->emailCampaign->getEmailCampaigns();
        $responseData = ResponseHelper::toArray($response);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('pagination', $responseData);
        $this->assertCount(1, $responseData['data']);
        $this->assertEquals(4567, $responseData['data'][0]['id']);
        $this->assertEquals(1, $responseData['pagination']['token']);
    }

    public function testGetEmailCampaignsWithFilters(): void
    {
        $perPage = 25;
        $search = 'spring';
        $token = 2;

        $this->emailCampaign->expects($this->once())
            ->method('httpGet')
            ->with(
                self::BASE_PATH,
                [
                    'per_page' => $perPage,
                    // name filter must serialize to `search`, not `name`
                    'search' => $search,
                    'token' => $token,
                ]
            )
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode([
                        'data' => [$this->getExpectedEmailCampaignResponse()],
                        'pagination' => $this->getExpectedPagination(),
                    ])
                )
            );

        $response = $this->emailCampaign->getEmailCampaigns($perPage, $search, $token);
        $responseData = ResponseHelper::toArray($response);

        $this->assertCount(1, $responseData['data']);
    }

    public function testGetEmailCampaign(): void
    {
        $emailCampaignId = 4567;

        $this->emailCampaign->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_PATH . '/' . $emailCampaignId)
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(['data' => $this->getExpectedEmailCampaignResponse()])
                )
            );

        $response = $this->emailCampaign->getEmailCampaign($emailCampaignId);
        $responseData = ResponseHelper::toArray($response);

        // single-campaign responses are wrapped in a `data` envelope
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals($emailCampaignId, $responseData['data']['id']);
        $this->assertEquals(EmailCampaignInterface::STATE_DRAFT, $responseData['data']['current_state']);
        $this->assertEquals(self::FAKE_DOMAIN_ID, $responseData['data']['domain_id']);
        $this->assertEquals([55, 56], $responseData['data']['contact_list_ids']);
        $this->assertEquals([12], $responseData['data']['contact_segment_ids']);
    }

    public function testGetEmailCampaignFailsWithNotFoundError(): void
    {
        $emailCampaignId = 999;

        $this->emailCampaign->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_PATH . '/' . $emailCampaignId)
            ->willReturn(
                new Response(404, ['Content-Type' => 'application/json'], json_encode(['error' => 'Not Found']))
            );

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('The requested entity has not been found. Errors: Not Found.');

        $this->emailCampaign->getEmailCampaign($emailCampaignId);
    }

    public function testCreateEmailCampaign(): void
    {
        $createDto = $this->getCreateEmailCampaignDTO();

        $this->emailCampaign->expects($this->once())
            ->method('httpPost')
            ->with(
                self::BASE_PATH,
                [],
                $createDto->toArray()
            )
            ->willReturn(
                new Response(
                    201,
                    ['Content-Type' => 'application/json'],
                    json_encode(['data' => $this->getExpectedEmailCampaignResponse()])
                )
            );

        $response = $this->emailCampaign->createEmailCampaign($createDto);
        $responseData = ResponseHelper::toArray($response);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals('Spring Sale', $responseData['data']['name']);
    }

    public function testCreateEmailCampaignSerializesFlatBody(): void
    {
        $createDto = $this->getCreateEmailCampaignDTO();
        $payload = $createDto->toArray();

        // the body is flat — no `email_campaign` wrapper key
        $this->assertEquals('Spring Sale', $payload['name']);
        $this->assertEquals(self::FAKE_DOMAIN_ID, $payload['domain_id']);
        $this->assertEquals('news', $payload['from_local_part']);
        $this->assertEquals(
            ['display_name' => 'Acme Support', 'local_part' => 'support', 'domain' => 'acme.com'],
            $payload['reply_to']
        );
        $this->assertEquals(
            [
                'subject' => 'Spring is here — 30% off',
                'body_html' => '<html><body><h1>Hi {{first_name}}!</h1>'
                    . '<p><a href="__unsubscribe_url__">Unsubscribe</a></p></body></html>',
                'merge_tags' => ['first_name'],
            ],
            $payload['template_attributes']
        );
        $this->assertEquals(EmailCampaignInterface::DELIVERY_MODE_GRADUAL, $payload['delivery_mode']);
        $this->assertEquals(['emails_per_hour' => 1000], $payload['delivery_options']);
        $this->assertEquals([55, 56], $payload['contact_list_ids']);
        $this->assertEquals([12], $payload['contact_segment_ids']);
    }

    public function testCreateEmailCampaignFailsWithValidationErrors(): void
    {
        $invalidDto = new CreateEmailCampaign(
            name: 'Spring Sale',
            domainId: 999,
            fromLocalPart: 'news',
            templateAttributes: new TemplateAttributes(subject: 'Spring is here'),
        );

        $this->emailCampaign->expects($this->once())
            ->method('httpPost')
            ->with(
                self::BASE_PATH,
                [],
                $invalidDto->toArray()
            )
            ->willReturn(
                new Response(
                    422,
                    ['Content-Type' => 'application/json'],
                    json_encode(['errors' => ['domain_id' => ['must exist']]])
                )
            );

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('Errors: domain_id -> must exist.');

        $this->emailCampaign->createEmailCampaign($invalidDto);
    }

    public function testUpdateEmailCampaign(): void
    {
        $emailCampaignId = 4567;
        $updateDto = $this->getUpdateEmailCampaignDTO();

        $this->emailCampaign->expects($this->once())
            ->method('httpPatch')
            ->with(
                self::BASE_PATH . '/' . $emailCampaignId,
                [],
                $updateDto->toArray()
            )
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(['data' => $this->getExpectedEmailCampaignResponse()])
                )
            );

        $response = $this->emailCampaign->updateEmailCampaign($emailCampaignId, $updateDto);
        $responseData = ResponseHelper::toArray($response);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals($emailCampaignId, $responseData['data']['id']);
    }

    public function testUpdateEmailCampaignSerializesProvidedAttributesOnly(): void
    {
        $updateDto = $this->getUpdateEmailCampaignDTO();
        $payload = $updateDto->toArray();

        $this->assertEquals(
            [
                'name' => 'Spring Sale (updated)',
                'delivery_mode' => EmailCampaignInterface::DELIVERY_MODE_GRADUAL,
                'delivery_options' => ['emails_per_hour' => 1000],
                'template_attributes' => ['subject' => 'New subject'],
                'contact_list_ids' => [55],
            ],
            $payload
        );
    }

    public function testUpdateEmailCampaignKeepsEmptyAudienceArrays(): void
    {
        // `[]` must survive serialization — it clears the audience, unlike an omitted field
        $payload = (new UpdateEmailCampaign(contactListIds: [], contactSegmentIds: []))->toArray();

        $this->assertEquals(['contact_list_ids' => [], 'contact_segment_ids' => []], $payload);
    }

    public function testUpdateEmailCampaignRejectsEmptyPayload(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('At least one attribute must be provided to update an email campaign.');

        (new UpdateEmailCampaign())->toArray();
    }

    public function testDeleteEmailCampaign(): void
    {
        $emailCampaignId = 4567;

        $this->emailCampaign->expects($this->once())
            ->method('httpDelete')
            ->with(self::BASE_PATH . '/' . $emailCampaignId)
            ->willReturn(new Response(204));

        $response = $this->emailCampaign->deleteEmailCampaign($emailCampaignId);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getBody()->__toString());
    }

    public function testStartEmailCampaign(): void
    {
        $emailCampaignId = 4567;

        $this->emailCampaign->expects($this->once())
            ->method('httpPost')
            ->with(self::BASE_PATH . '/' . $emailCampaignId . '/start')
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(['data' => $this->getExpectedEmailCampaignResponse(
                        EmailCampaignInterface::STATE_QUEUED
                    )])
                )
            );

        $response = $this->emailCampaign->startEmailCampaign($emailCampaignId);
        $responseData = ResponseHelper::toArray($response);

        $this->assertEquals(EmailCampaignInterface::STATE_QUEUED, $responseData['data']['current_state']);
    }

    public function testScheduleEmailCampaign(): void
    {
        $emailCampaignId = 4567;
        $datetime = '2026-06-01T09:00:00.000Z';

        $this->emailCampaign->expects($this->once())
            ->method('httpPost')
            ->with(
                self::BASE_PATH . '/' . $emailCampaignId . '/schedule',
                [],
                ['datetime' => $datetime]
            )
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(['data' => $this->getExpectedEmailCampaignResponse(
                        EmailCampaignInterface::STATE_SCHEDULED
                    )])
                )
            );

        $response = $this->emailCampaign->scheduleEmailCampaign($emailCampaignId, $datetime);
        $responseData = ResponseHelper::toArray($response);

        $this->assertEquals(EmailCampaignInterface::STATE_SCHEDULED, $responseData['data']['current_state']);
    }

    public function testScheduleEmailCampaignAcceptsDateTime(): void
    {
        $emailCampaignId = 4567;
        $datetime = new \DateTimeImmutable('2026-06-01 09:00:00', new \DateTimeZone('UTC'));

        $this->emailCampaign->expects($this->once())
            ->method('httpPost')
            ->with(
                self::BASE_PATH . '/' . $emailCampaignId . '/schedule',
                [],
                ['datetime' => '2026-06-01T09:00:00+00:00']
            )
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(['data' => $this->getExpectedEmailCampaignResponse(
                        EmailCampaignInterface::STATE_SCHEDULED
                    )])
                )
            );

        $this->emailCampaign->scheduleEmailCampaign($emailCampaignId, $datetime);
    }

    public function testCancelEmailCampaign(): void
    {
        $emailCampaignId = 4567;

        $this->emailCampaign->expects($this->once())
            ->method('httpPost')
            ->with(self::BASE_PATH . '/' . $emailCampaignId . '/cancel')
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(['data' => $this->getExpectedEmailCampaignResponse()])
                )
            );

        $response = $this->emailCampaign->cancelEmailCampaign($emailCampaignId);
        $responseData = ResponseHelper::toArray($response);

        $this->assertEquals(EmailCampaignInterface::STATE_DRAFT, $responseData['data']['current_state']);
    }

    public function testCancelEmailCampaignFailsWhenNotScheduled(): void
    {
        $emailCampaignId = 4567;

        $this->emailCampaign->expects($this->once())
            ->method('httpPost')
            ->with(self::BASE_PATH . '/' . $emailCampaignId . '/cancel')
            ->willReturn(
                new Response(
                    422,
                    ['Content-Type' => 'application/json'],
                    json_encode(['errors' => 'Campaign is not scheduled'])
                )
            );

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('Errors: Campaign is not scheduled.');

        $this->emailCampaign->cancelEmailCampaign($emailCampaignId);
    }

    public function testTerminateEmailCampaign(): void
    {
        $emailCampaignId = 4567;

        $this->emailCampaign->expects($this->once())
            ->method('httpPost')
            ->with(self::BASE_PATH . '/' . $emailCampaignId . '/terminate')
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(['data' => $this->getExpectedEmailCampaignResponse(
                        EmailCampaignInterface::STATE_TERMINATING
                    )])
                )
            );

        $response = $this->emailCampaign->terminateEmailCampaign($emailCampaignId);
        $responseData = ResponseHelper::toArray($response);

        $this->assertEquals(EmailCampaignInterface::STATE_TERMINATING, $responseData['data']['current_state']);
    }

    public function testResetEmailCampaign(): void
    {
        $emailCampaignId = 4567;

        $this->emailCampaign->expects($this->once())
            ->method('httpPost')
            ->with(self::BASE_PATH . '/' . $emailCampaignId . '/reset')
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(['data' => $this->getExpectedEmailCampaignResponse()])
                )
            );

        $response = $this->emailCampaign->resetEmailCampaign($emailCampaignId);
        $responseData = ResponseHelper::toArray($response);

        $this->assertEquals(EmailCampaignInterface::STATE_DRAFT, $responseData['data']['current_state']);
    }

    public function testGetEmailCampaignStats(): void
    {
        $emailCampaignId = 4567;

        $this->emailCampaign->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_PATH . '/' . $emailCampaignId . '/stats', [])
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(['data' => $this->getExpectedStatsResponse()])
                )
            );

        $response = $this->emailCampaign->getEmailCampaignStats($emailCampaignId);
        $responseData = ResponseHelper::toArray($response);

        // stats are wrapped in a `data` envelope
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals(1450, $responseData['data']['delivery_count']);
        $this->assertEquals(0.9667, $responseData['data']['delivery_rate']);
    }

    public function testGetEmailCampaignStatsWithDateRange(): void
    {
        $emailCampaignId = 4567;
        $startDate = '2026-05-01';
        $endDate = '2026-05-31';

        $this->emailCampaign->expects($this->once())
            ->method('httpGet')
            ->with(
                self::BASE_PATH . '/' . $emailCampaignId . '/stats',
                ['start_date' => $startDate, 'end_date' => $endDate]
            )
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(['data' => $this->getExpectedStatsResponse()])
                )
            );

        $response = $this->emailCampaign->getEmailCampaignStats($emailCampaignId, $startDate, $endDate);
        $responseData = ResponseHelper::toArray($response);

        $this->assertEquals(1500, $responseData['data']['sent_count']);
    }

    public function testGetAccountId(): void
    {
        $this->assertEquals(self::FAKE_ACCOUNT_ID, $this->emailCampaign->getAccountId());
    }

    private function getCreateEmailCampaignDTO(): CreateEmailCampaign
    {
        return new CreateEmailCampaign(
            name: 'Spring Sale',
            domainId: self::FAKE_DOMAIN_ID,
            fromLocalPart: 'news',
            templateAttributes: new TemplateAttributes(
                subject: 'Spring is here — 30% off',
                bodyHtml: '<html><body><h1>Hi {{first_name}}!</h1>'
                    . '<p><a href="__unsubscribe_url__">Unsubscribe</a></p></body></html>',
                mergeTags: ['first_name'],
            ),
            fromDisplayName: 'Acme Marketing',
            replyTo: new ReplyTo(displayName: 'Acme Support', localPart: 'support', domain: 'acme.com'),
            deliveryMode: EmailCampaignInterface::DELIVERY_MODE_GRADUAL,
            deliveryOptions: ['emails_per_hour' => 1000],
            contactListIds: [55, 56],
            contactSegmentIds: [12],
        );
    }

    private function getUpdateEmailCampaignDTO(): UpdateEmailCampaign
    {
        return new UpdateEmailCampaign(
            name: 'Spring Sale (updated)',
            deliveryMode: EmailCampaignInterface::DELIVERY_MODE_GRADUAL,
            deliveryOptions: ['emails_per_hour' => 1000],
            templateAttributes: new TemplateAttributes(subject: 'New subject'),
            contactListIds: [55],
        );
    }

    private function getExpectedEmailCampaignResponse(
        string $currentState = EmailCampaignInterface::STATE_DRAFT
    ): array {
        return [
            'id' => 4567,
            'domain_id' => self::FAKE_DOMAIN_ID,
            'domain_name' => 'acme.com',
            'name' => 'Spring Sale',
            'from_local_part' => 'news',
            'from_display_name' => 'Acme Marketing',
            'reply_to' => ['display_name' => 'Acme Support', 'local_part' => 'support', 'domain' => 'acme.com'],
            'current_state' => $currentState,
            'current_state_metadata' => $currentState === EmailCampaignInterface::STATE_SCHEDULED
                ? ['scheduled_at' => '2026-06-01T09:00:00.000Z']
                : [],
            'created_at' => '2026-05-01T10:15:00.000Z',
            'updated_at' => '2026-05-02T09:00:00.000Z',
            'last_started_at' => null,
            'recipient_total_count' => null,
            'contact_list_ids' => [55, 56],
            'contact_segment_ids' => [12],
            'delivery_mode' => 'rapid',
            'delivery_options' => ['emails_per_hour' => null],
            'template' => [
                'id' => 789,
                'subject' => 'Spring is here — 30% off',
                'merge_tags' => ['first_name'],
                'body_html' => '<html><body><h1>Hi {{first_name}}!</h1>'
                    . '<p><a href="__unsubscribe_url__">Unsubscribe</a></p></body></html>',
                'body_text' => null,
            ],
        ];
    }

    private function getExpectedStatsResponse(): array
    {
        return [
            'delivery_count' => 1450,
            'open_count' => 820,
            'click_count' => 310,
            'bounce_count' => 30,
            'unsubscription_count' => 12,
            'sent_count' => 1500,
            'spam_count' => 5,
            'delivery_rate' => 0.9667,
            'open_rate' => 0.5655,
            'click_rate' => 0.2138,
            'bounce_rate' => 0.02,
            'spam_rate' => 0.0033,
            'unsubscription_rate' => 0.0083,
        ];
    }

    private function getExpectedPagination(): array
    {
        return [
            'token' => 1,
            'prev_token' => null,
            'next_token' => 2,
            'first_url' => 'https://mailtrap.io/api/email_campaigns?per_page=50&token=1',
            'prev_url' => null,
            'current_url' => 'https://mailtrap.io/api/email_campaigns?per_page=50&token=1',
            'next_url' => 'https://mailtrap.io/api/email_campaigns?per_page=50&token=2',
        ];
    }
}
