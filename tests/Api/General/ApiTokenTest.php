<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\General;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\General\ApiToken;
use Mailtrap\DTO\Request\Permission\CreateOrUpdatePermission;
use Mailtrap\DTO\Request\Permission\PermissionInterface;
use Mailtrap\DTO\Request\Permission\Permissions;
use Mailtrap\Exception\HttpClientException;
use Mailtrap\Exception\RuntimeException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers ApiToken
 *
 * Class ApiTokenTest
 */
class ApiTokenTest extends MailtrapTestCase
{
    private ?ApiToken $apiToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiToken = $this->getMockBuilder(ApiToken::class)
            ->onlyMethods(['httpGet', 'httpPost', 'httpDelete'])
            ->setConstructorArgs([$this->getConfigMock(), self::FAKE_ACCOUNT_ID])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->apiToken = null;
        parent::tearDown();
    }

    public function testGetApiTokens(): void
    {
        $this->apiToken->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/api_tokens')
            ->willReturn(
                new Response(200, ['Content-Type' => 'application/json'], json_encode([$this->getExpectedApiTokenResponse()]))
            );

        $response = $this->apiToken->getApiTokens();
        $responseData = ResponseHelper::toArray($response);

        $this->assertCount(1, $responseData);
        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('name', $responseData[0]);
        $this->assertArrayHasKey('last_4_digits', $responseData[0]);
        $this->assertArrayHasKey('resources', $responseData[0]);
    }

    public function testGetApiTokensFailsWithUnauthorizedError(): void
    {
        $expectedErrorResponse = [
            'errors' => 'Incorrect API token',
        ];

        $this->apiToken->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/api_tokens')
            ->willReturn(
                new Response(401, ['Content-Type' => 'application/json'], json_encode($expectedErrorResponse))
            );

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('Incorrect API token');

        $this->apiToken->getApiTokens();
    }

    public function testGetApiTokenById(): void
    {
        $apiTokenId = 1;

        $this->apiToken->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/api_tokens/' . $apiTokenId)
            ->willReturn(
                new Response(200, ['Content-Type' => 'application/json'], json_encode($this->getExpectedApiTokenResponse()))
            );

        $response = $this->apiToken->getApiToken($apiTokenId);
        $responseData = ResponseHelper::toArray($response);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($apiTokenId, $responseData['id']);
    }

    public function testGetApiTokenFailsWithNotFoundError(): void
    {
        $apiTokenId = 999;

        $this->apiToken->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/api_tokens/' . $apiTokenId)
            ->willReturn(
                new Response(404, ['Content-Type' => 'application/json'], json_encode(['error' => 'Not Found']))
            );

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('The requested entity has not been found. Errors: Not Found.');

        $this->apiToken->getApiToken($apiTokenId);
    }

    public function testCreateApiToken(): void
    {
        $permissions = $this->getPermissions();
        $name = 'My API token';

        $this->apiToken->expects($this->once())
            ->method('httpPost')
            ->with(
                AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/api_tokens',
                [],
                [
                    'name' => $name,
                    'resources' => [
                        [
                            'resource_id' => (string) self::FAKE_ACCOUNT_ID,
                            'resource_type' => PermissionInterface::TYPE_ACCOUNT,
                            'access_level' => '1000',
                        ],
                    ],
                ]
            )
            ->willReturn(
                new Response(
                    201,
                    ['Content-Type' => 'application/json'],
                    json_encode($this->getExpectedApiTokenResponse() + ['token' => 'fresh-secret-token-value'])
                )
            );

        $response = $this->apiToken->createApiToken($name, $permissions);
        $responseData = ResponseHelper::toArray($response);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('token', $responseData);
        $this->assertEquals('fresh-secret-token-value', $responseData['token']);
    }

    public function testCreateApiTokenFailsWithoutPermissions(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('At least one "permission" object must be provided');

        $this->apiToken->createApiToken('My token', new Permissions());
    }

    public function testDeleteApiToken(): void
    {
        $apiTokenId = 1;

        $this->apiToken->expects($this->once())
            ->method('httpDelete')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/api_tokens/' . $apiTokenId)
            ->willReturn(new Response(204));

        $response = $this->apiToken->deleteApiToken($apiTokenId);

        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testResetApiToken(): void
    {
        $apiTokenId = 1;

        $this->apiToken->expects($this->once())
            ->method('httpPost')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/api_tokens/' . $apiTokenId . '/reset')
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode($this->getExpectedApiTokenResponse() + ['token' => 'rotated-secret-token-value'])
                )
            );

        $response = $this->apiToken->resetApiToken($apiTokenId);
        $responseData = ResponseHelper::toArray($response);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('token', $responseData);
        $this->assertEquals('rotated-secret-token-value', $responseData['token']);
    }

    public function testResetApiTokenFailsWhenAlreadyReset(): void
    {
        $apiTokenId = 1;

        $this->apiToken->expects($this->once())
            ->method('httpPost')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/api_tokens/' . $apiTokenId . '/reset')
            ->willReturn(
                new Response(
                    422,
                    ['Content-Type' => 'application/json'],
                    json_encode(['errors' => ['base' => ['API token was already reset']]])
                )
            );

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('API token was already reset');

        $this->apiToken->resetApiToken($apiTokenId);
    }

    private function getPermissions(): Permissions
    {
        return new Permissions(
            new CreateOrUpdatePermission(
                self::FAKE_ACCOUNT_ID,
                PermissionInterface::TYPE_ACCOUNT,
                1000
            )
        );
    }

    private function getExpectedApiTokenResponse(): array
    {
        return [
            'id' => 1,
            'name' => 'My API token',
            'last_4_digits' => 'abcd',
            'created_by' => 'John Doe',
            'expires_at' => null,
            'resources' => [
                [
                    'resource_type' => PermissionInterface::TYPE_ACCOUNT,
                    'resource_id' => self::FAKE_ACCOUNT_ID,
                    'access_level' => 1000,
                ],
            ],
        ];
    }
}
