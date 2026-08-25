<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\Sending;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Sending\CompanyInfo as CompanyInfoApi;
use Mailtrap\DTO\Request\Domain\CompanyInfo;
use Mailtrap\DTO\Request\Domain\CreateCompanyInfo;
use Mailtrap\DTO\Request\Domain\UpdateCompanyInfo;
use Mailtrap\Exception\HttpClientException;
use Mailtrap\Exception\InvalidArgumentException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers CompanyInfoApi
 *
 * Class CompanyInfoTest
 */
class CompanyInfoTest extends MailtrapTestCase
{
    private const DOMAIN_ID = 12345;

    private ?CompanyInfoApi $companyInfo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->companyInfo = $this->getMockBuilder(CompanyInfoApi::class)
            ->onlyMethods(['httpGet', 'httpPost', 'httpPatch'])
            ->setConstructorArgs([$this->getConfigMock(), self::DOMAIN_ID])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->companyInfo = null;
        parent::tearDown();
    }

    public function testGetCompanyInfo(): void
    {
        $this->companyInfo->expects($this->once())
            ->method('httpGet')
            ->with($this->getExpectedPath())
            ->willReturn(
                new Response(200, ['Content-Type' => 'application/json'], $this->getExpectedCompanyInfoResponse())
            );

        $response = $this->companyInfo->getCompanyInfo();
        $responseData = ResponseHelper::toArray($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('data', $responseData);
        $this->assertSame('Mailtrap', $responseData['data']['name']);
        $this->assertSame(CompanyInfo::INFO_LEVEL_BUSINESS, $responseData['data']['info_level']);
    }

    public function testGetCompanyInfoNotFound(): void
    {
        $errorResponse = ['error' => 'Not Found'];

        $this->companyInfo->expects($this->once())
            ->method('httpGet')
            ->with($this->getExpectedPath())
            ->willReturn(new Response(404, ['Content-Type' => 'application/json'], json_encode($errorResponse)));

        $this->expectException(HttpClientException::class);

        $this->companyInfo->getCompanyInfo();
    }

    public function testCreateCompanyInfo(): void
    {
        $this->companyInfo->expects($this->once())
            ->method('httpPost')
            ->with(
                $this->getExpectedPath(),
                [],
                [
                    'company_info' => [
                        'name' => 'Mailtrap',
                        'address' => '123 Main St',
                        'city' => 'San Francisco',
                        'country' => 'US',
                        'zip_code' => '94105',
                        'website_url' => 'https://mailtrap.io',
                        'info_level' => CompanyInfo::INFO_LEVEL_BUSINESS,
                    ]
                ]
            )
            ->willReturn(
                new Response(200, ['Content-Type' => 'application/json'], $this->getExpectedCompanyInfoResponse())
            );

        $response = $this->companyInfo->createCompanyInfo(
            new CreateCompanyInfo(
                name: 'Mailtrap',
                address: '123 Main St',
                city: 'San Francisco',
                country: 'US',
                zipCode: '94105',
                websiteUrl: 'https://mailtrap.io',
                infoLevel: CompanyInfo::INFO_LEVEL_BUSINESS
            )
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testCreateCompanyInfoWithAllOptionalFields(): void
    {
        $this->companyInfo->expects($this->once())
            ->method('httpPost')
            ->with(
                $this->getExpectedPath(),
                [],
                [
                    'company_info' => [
                        'name' => 'Mailtrap',
                        'address' => '123 Main St',
                        'city' => 'San Francisco',
                        'country' => 'US',
                        'zip_code' => '94105',
                        'website_url' => 'https://mailtrap.io',
                        'phone' => '+1-555-0100',
                        'privacy_policy_url' => 'https://mailtrap.io/privacy',
                        'terms_of_service_url' => 'https://mailtrap.io/terms',
                        'info_level' => CompanyInfo::INFO_LEVEL_INDIVIDUAL,
                    ]
                ]
            )
            ->willReturn(
                new Response(200, ['Content-Type' => 'application/json'], $this->getExpectedCompanyInfoResponse())
            );

        $response = $this->companyInfo->createCompanyInfo(
            new CreateCompanyInfo(
                name: 'Mailtrap',
                address: '123 Main St',
                city: 'San Francisco',
                country: 'US',
                zipCode: '94105',
                websiteUrl: 'https://mailtrap.io',
                phone: '+1-555-0100',
                privacyPolicyUrl: 'https://mailtrap.io/privacy',
                termsOfServiceUrl: 'https://mailtrap.io/terms',
                infoLevel: CompanyInfo::INFO_LEVEL_INDIVIDUAL
            )
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testUpdateCompanyInfoSendsOnlyProvidedFields(): void
    {
        $this->companyInfo->expects($this->once())
            ->method('httpPatch')
            ->with(
                $this->getExpectedPath(),
                [],
                [
                    'company_info' => [
                        'city' => 'New York',
                        'zip_code' => '10001',
                    ]
                ]
            )
            ->willReturn(
                new Response(200, ['Content-Type' => 'application/json'], $this->getExpectedCompanyInfoResponse())
            );

        $response = $this->companyInfo->updateCompanyInfo(
            new UpdateCompanyInfo(city: 'New York', zipCode: '10001')
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testUpdateCompanyInfoWithEmptyPayload(): void
    {
        $this->companyInfo->expects($this->never())->method('httpPatch');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one updatable field must be provided to update company info');

        $this->companyInfo->updateCompanyInfo(new UpdateCompanyInfo());
    }

    private function getExpectedPath(): string
    {
        return AbstractApi::DEFAULT_HOST . '/api/domains/' . self::DOMAIN_ID . '/company_info';
    }

    private function getExpectedCompanyInfoResponse(): string
    {
        return json_encode([
            'data' => [
                'name' => 'Mailtrap',
                'address' => '123 Main St',
                'city' => 'San Francisco',
                'country' => 'US',
                'phone' => '+1-555-0100',
                'zip_code' => '94105',
                'privacy_policy_url' => 'https://mailtrap.io/privacy',
                'terms_of_service_url' => 'https://mailtrap.io/terms',
                'website_url' => 'https://mailtrap.io',
                'info_level' => CompanyInfo::INFO_LEVEL_BUSINESS,
            ]
        ]);
    }
}
