<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\Sending;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Sending\Domain;
use Mailtrap\DTO\Request\Domain\UpdateDomain;
use Mailtrap\Exception\HttpClientException;
use Mailtrap\Exception\InvalidArgumentException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\AssertsDeprecationsTrait;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers Domain
 *
 * Class DomainTest
 */
class DomainTest extends MailtrapTestCase
{
    use AssertsDeprecationsTrait;

    private ?Domain $domain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->domain = $this->getMockBuilder(Domain::class)
            ->onlyMethods(['httpGet', 'httpPost', 'httpPatch', 'httpDelete'])
            ->setConstructorArgs([$this->getConfigMock(), self::FAKE_ACCOUNT_ID])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->domain = null;
        parent::tearDown();
    }

    public function testGetDomains(): void
    {
        $expectedResponseBody = $this->getExpectedDomainsResponse();

        $this->domain->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/sending_domains')
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], $expectedResponseBody));

        $response = $this->domain->getDomains();
        $responseData = ResponseHelper::toArray($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('id', $responseData['data'][0]);
        $this->assertArrayHasKey('domain_name', $responseData['data'][0]);
        $this->assertArrayHasKey('compliance_status', $responseData['data'][0]);
        $this->assertArrayHasKey('dns_verified', $responseData['data'][0]);
        $this->assertArrayHasKey('permissions', $responseData['data'][0]);
    }

    public function testCreateDomain(): void
    {
        $domainName = 'example.com';
        $expectedResponseBody = $this->getExpectedDomainResponse($domainName);

        $this->domain->expects($this->once())
            ->method('httpPost')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/sending_domains',
                [],
                [
                    'sending_domain' => [
                        'domain_name' => $domainName
                    ]
                ]
            )
            ->willReturn(new Response(201, ['Content-Type' => 'application/json'], $expectedResponseBody));

        $response = $this->domain->createDomain($domainName);
        $responseData = ResponseHelper::toArray($response);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('domain_name', $responseData);
        $this->assertArrayHasKey('compliance_status', $responseData);
        $this->assertArrayHasKey('dns_verified', $responseData);
        $this->assertArrayHasKey('permissions', $responseData);
        $this->assertSame($domainName, $responseData['domain_name']);
    }

    public function testCreateDomainInvalid(): void
    {
        $domainName = '';
        $errorResponse = [
            'error' => [
                'sending_domain' => [
                    'domain_name' => ['can\'t be blank']
                ]
            ]
        ];

        $this->domain->expects($this->once())
            ->method('httpPost')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/sending_domains',
                [],
                [
                    'sending_domain' => [
                        'domain_name' => $domainName
                    ]
                ]
            )
            ->willReturn(new Response(422, ['Content-Type' => 'application/json'], json_encode($errorResponse)));

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('Errors: sending_domain -> domain_name -> can\'t be blank.');

        $this->domain->createDomain($domainName);
    }

    public function testGetDomainById(): void
    {
        $domainId = 12345;
        $expectedResponseBody = $this->getExpectedDomainResponse('example.com', $domainId);

        $this->domain->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/sending_domains/' . $domainId)
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], $expectedResponseBody));

        $response = $this->domain->getDomainById($domainId);
        $responseData = ResponseHelper::toArray($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('id', $responseData);
        $this->assertSame($domainId, $responseData['id']);
        $this->assertArrayHasKey('domain_name', $responseData);
        $this->assertArrayHasKey('compliance_status', $responseData);
        $this->assertArrayHasKey('dns_verified', $responseData);
        $this->assertArrayHasKey('permissions', $responseData);
    }

    public function testGetDomainByIdNotFound(): void
    {
        $domainId = 99999;
        $errorResponse = [
            'error' => 'Domain not found'
        ];

        $this->domain->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/sending_domains/' . $domainId)
            ->willReturn(new Response(404, ['Content-Type' => 'application/json'], json_encode($errorResponse)));

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('The requested entity has not been found. Errors: Domain not found.');

        $this->domain->getDomainById($domainId);
    }

    public function testDeleteDomain(): void
    {
        $domainId = 12345;

        $this->domain->expects($this->once())
            ->method('httpDelete')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/sending_domains/' . $domainId)
            ->willReturn(new Response(204));

        $response = $this->domain->deleteDomain($domainId);
        $this->assertSame(204, $response->getStatusCode());
    }

    public function testDeleteDomainNotFound(): void
    {
        $domainId = 99999;
        $errorResponse = [
            'error' => 'Domain not found'
        ];

        $this->domain->expects($this->once())
            ->method('httpDelete')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/sending_domains/' . $domainId)
            ->willReturn(new Response(404, ['Content-Type' => 'application/json'], json_encode($errorResponse)));

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('The requested entity has not been found. Errors: Domain not found.');

        $this->domain->deleteDomain($domainId);
    }

    public function testSendDomainSetupInstructions(): void
    {
        $domainId = 12345;
        $email = 'devops@example.com';

        $this->domain->expects($this->once())
            ->method('httpPost')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/sending_domains/' . $domainId . '/send_setup_instructions',
                [],
                ['email' => $email]
            )
            ->willReturn(new Response(204));

        $response = $this->domain->sendDomainSetupInstructions($domainId, $email);

        $this->assertSame(204, $response->getStatusCode());
    }

    public function testSendDomainSetupInstructionsNotFound(): void
    {
        $domainId = 99999;
        $email = 'devops@example.com';
        $errorResponse = [
            'error' => 'Domain not found'
        ];

        $this->domain->expects($this->once())
            ->method('httpPost')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/sending_domains/' . $domainId . '/send_setup_instructions',
                [],
                ['email' => $email]
            )
            ->willReturn(new Response(404, ['Content-Type' => 'application/json'], json_encode($errorResponse)));

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('The requested entity has not been found. Errors: Domain not found.');

        $this->domain->sendDomainSetupInstructions($domainId, $email);
    }

    private function getExpectedDomainsResponse(): string
    {
        return json_encode([
            "data" => [
                [
                    "id" => 435,
                    "domain_name" => "mailtrap.io",
                    "demo" => false,
                    "compliance_status" => "compliant",
                    "dns_verified" => true,
                    "dns_verified_at" => "2024-12-26T09:40:44.161Z",
                    "dns_records" => [
                        [
                            "key" => "verification",
                            "domain" => "ve6wza2rbpe60x7z.mailtrap.io",
                            "type" => "CNAME",
                            "value" => "smtp.mailtrap.live",
                            "status" => "pass",
                            "name" => "ve6wza2rbpe60x7z"
                        ],
                        [
                            "key" => "spf",
                            "domain" => "mailtrap.io",
                            "type" => "TXT",
                            "value" => "v=spf1 include:_spf.smtp.mailtrap.live ~all",
                            "status" => "pass",
                            "name" => ""
                        ]
                    ],
                    "open_tracking_enabled" => true,
                    "click_tracking_enabled" => true,
                    "auto_unsubscribe_link_enabled" => true,
                    "custom_domain_tracking_enabled" => true,
                    "health_alerts_enabled" => true,
                    "critical_alerts_enabled" => true,
                    "alert_recipient_email" => "john.doe@mailtrap.io",
                    "permissions" => [
                        "can_read" => true,
                        "can_update" => true,
                        "can_destroy" => true
                    ]
                ]
            ]
        ]);
    }

    public function testUpdateDomain(): void
    {
        $domainId = 12345;
        $expectedResponseBody = $this->getExpectedDomainResponse('example.com', $domainId);

        $this->domain->expects($this->once())
            ->method('httpPatch')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/sending_domains/' . $domainId,
                [],
                [
                    'sending_domain' => [
                        'open_tracking_enabled' => true,
                        'click_tracking_enabled' => true,
                        'tracking_opt_out_enabled' => true,
                        'auto_unsubscribe_link_enabled' => false,
                        'inbound_enabled' => false,
                    ]
                ]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], $expectedResponseBody));

        $response = $this->domain->updateDomain(
            $domainId,
            new UpdateDomain(
                openTrackingEnabled: true,
                clickTrackingEnabled: true,
                trackingOptOutEnabled: true,
                autoUnsubscribeLinkEnabled: false,
                inboundEnabled: false
            )
        );
        $responseData = ResponseHelper::toArray($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($domainId, $responseData['id']);
    }

    public function testUpdateDomainSendsOnlyProvidedFields(): void
    {
        $domainId = 12345;
        $expectedResponseBody = $this->getExpectedDomainResponse('example.com', $domainId);

        $this->domain->expects($this->once())
            ->method('httpPatch')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/sending_domains/' . $domainId,
                [],
                [
                    'sending_domain' => [
                        'tracking_opt_out_enabled' => false,
                    ]
                ]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], $expectedResponseBody));

        $response = $this->domain->updateDomain(
            $domainId,
            new UpdateDomain(trackingOptOutEnabled: false)
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testUpdateDomainWithEmptyPayload(): void
    {
        $this->domain->expects($this->never())->method('httpPatch');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'At least one updatable field must be provided to update a domain'
        );

        $this->domain->updateDomain(12345, new UpdateDomain());
    }

    /**
     * @dataProvider deprecatedMethodsProvider
     */
    public function testDeprecatedMethods(string $method, array $arguments, string $httpMethod, string $newMethod): void
    {
        $this->domain->expects($this->once())
            ->method($httpMethod)
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], $this->getExpectedDomainResponse('example.com')));

        $response = $this->assertDeprecationTriggered(
            sprintf(
                'Mailtrap\Api\Sending\Domain::%s() is deprecated and will be removed in a future major release; use Mailtrap\Api\Sending\Domain::%s() instead.',
                $method,
                $newMethod
            ),
            fn () => $this->domain->{$method}(...$arguments)
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function deprecatedMethodsProvider(): iterable
    {
        yield ['getSendingDomains', [], 'httpGet', 'getDomains'];
        yield ['createSendingDomain', ['example.com'], 'httpPost', 'createDomain'];
        yield ['updateSendingDomain', [12345, new UpdateDomain(openTrackingEnabled: false)], 'httpPatch', 'updateDomain'];
        yield ['deleteSendingDomain', [12345], 'httpDelete', 'deleteDomain'];
    }

    private function getExpectedDomainResponse(string $domainName, ?int $domainId = null): string
    {
        return json_encode([
            "id" => $domainId ?? 435,
            "domain_name" => $domainName,
            "demo" => false,
            "compliance_status" => "compliant",
            "dns_verified" => true,
            "dns_verified_at" => "2024-12-26T09:40:44.161Z",
            "dns_records" => [
                [
                    "key" => "verification",
                    "domain" => "ve6wza2rbpe60x7z." . $domainName,
                    "type" => "CNAME",
                    "value" => "smtp.mailtrap.live",
                    "status" => "pass",
                    "name" => "ve6wza2rbpe60x7z"
                ],
                [
                    "key" => "spf",
                    "domain" => $domainName,
                    "type" => "TXT",
                    "value" => "v=spf1 include:_spf.smtp.mailtrap.live ~all",
                    "status" => "pass",
                    "name" => ""
                ],
                [
                    "key" => "dkim1",
                    "domain" => "rwmt1._domainkey." . $domainName,
                    "type" => "CNAME",
                    "value" => "rwmt1.dkim.smtp.mailtrap.live",
                    "status" => "pass",
                    "name" => "rwmt1._domainkey"
                ]
            ],
            "open_tracking_enabled" => true,
            "click_tracking_enabled" => true,
            "auto_unsubscribe_link_enabled" => true,
            "custom_domain_tracking_enabled" => true,
            "health_alerts_enabled" => true,
            "critical_alerts_enabled" => true,
            "alert_recipient_email" => "john.doe@" . $domainName,
            "permissions" => [
                "can_read" => true,
                "can_update" => true,
                "can_destroy" => true
            ]
        ]);
    }
}
