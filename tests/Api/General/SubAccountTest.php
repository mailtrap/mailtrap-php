<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\General;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\General\SubAccount;
use Mailtrap\Exception\HttpClientException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers SubAccount
 *
 * Class SubAccountTest
 */
class SubAccountTest extends MailtrapTestCase
{
    private ?SubAccount $subAccount;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subAccount = $this->getMockBuilder(SubAccount::class)
            ->onlyMethods(['httpGet', 'httpPost'])
            ->setConstructorArgs([$this->getConfigMock(), self::FAKE_ORGANIZATION_ID])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->subAccount = null;
        parent::tearDown();
    }

    public function testGetSubAccounts(): void
    {
        $this->subAccount->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/organizations/' . self::FAKE_ORGANIZATION_ID . '/sub_accounts')
            ->willReturn(
                new Response(200, ['Content-Type' => 'application/json'], json_encode([$this->getExpectedSubAccountResponse()]))
            );

        $response = $this->subAccount->getSubAccounts();
        $responseData = ResponseHelper::toArray($response);

        $this->assertCount(1, $responseData);
        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('name', $responseData[0]);
    }

    public function testGetSubAccountsFailsWithForbidden(): void
    {
        $this->subAccount->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/organizations/' . self::FAKE_ORGANIZATION_ID . '/sub_accounts')
            ->willReturn(
                new Response(403, ['Content-Type' => 'application/json'], json_encode(['errors' => 'Access forbidden']))
            );

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('Access forbidden');

        $this->subAccount->getSubAccounts();
    }

    public function testCreateSubAccount(): void
    {
        $name = 'My new sub-account';

        $this->subAccount->expects($this->once())
            ->method('httpPost')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/organizations/' . self::FAKE_ORGANIZATION_ID . '/sub_accounts',
                [],
                ['account' => ['name' => $name]]
            )
            ->willReturn(
                new Response(201, ['Content-Type' => 'application/json'], json_encode($this->getExpectedSubAccountResponse(2, $name)))
            );

        $response = $this->subAccount->createSubAccount($name);
        $responseData = ResponseHelper::toArray($response);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($name, $responseData['name']);
    }

    public function testCreateSubAccountFailsWithValidationError(): void
    {
        $invalidName = '';

        $this->subAccount->expects($this->once())
            ->method('httpPost')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/organizations/' . self::FAKE_ORGANIZATION_ID . '/sub_accounts',
                [],
                ['account' => ['name' => $invalidName]]
            )
            ->willReturn(
                new Response(422, ['Content-Type' => 'application/json'], json_encode(['errors' => ["Name can't be blank"]]))
            );

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage("Name can't be blank");

        $this->subAccount->createSubAccount($invalidName);
    }

    private function getExpectedSubAccountResponse(int $id = 1, string $name = 'My sub-account'): array
    {
        return [
            'id' => $id,
            'name' => $name,
        ];
    }
}
