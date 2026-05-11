<?php

namespace Mailtrap\Tests\Api\General;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\General\Permission;
use Mailtrap\DTO\Request\Permission\CreateOrUpdatePermission;
use Mailtrap\DTO\Request\Permission\DestroyPermission;
use Mailtrap\DTO\Request\Permission\PermissionInterface;
use Mailtrap\DTO\Request\Permission\Permissions;
use Mailtrap\Exception\HttpClientException;
use Mailtrap\Exception\RuntimeException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers Permission
 *
 * Class PermissionTest
 */
class PermissionTest extends MailtrapTestCase
{
    /**
     * @var Permission
     */
    private $permission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->permission = $this->getMockBuilder(Permission::class)
            ->onlyMethods(['httpGet', 'httpPut'])
            ->setConstructorArgs([$this->getConfigMock(), self::FAKE_ACCOUNT_ID])
            ->getMock()
        ;
    }

    protected function tearDown(): void
    {
        $this->permission = null;

        parent::tearDown();
    }

    public function testValidGetResources(): void
    {
        $this->permission->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/permissions/resources')
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($this->getExpectedData())));

        $response = $this->permission->getResources();
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertCount(2, $responseData);
        $this->assertArrayHasKey('resources', array_shift($responseData));
    }

    public function test401InvalidGetResources(): void
    {
        $this->permission->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/permissions/resources')
            ->willReturn(new Response(401, ['Content-Type' => 'application/json'], json_encode(['error' => 'Incorrect API token'])));

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage(
            'Unauthorized. Make sure you are sending correct credentials with the request before retrying. Errors: Incorrect API token.'
        );

        $this->permission->getResources();
    }

    public function test403InvalidGetResources(): void
    {
        $this->permission->expects($this->once())
            ->method('httpGet')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/permissions/resources')
            ->willReturn(new Response(403, ['Content-Type' => 'application/json'], json_encode(['errors' => 'Access forbidden'])));

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage(
            'Forbidden. Make sure domain verification process is completed or check your permissions. Errors: Access forbidden.'
        );

        $this->permission->getResources();
    }

    /**
     * @dataProvider validUpdateDataProvider
     */
    public function testValidUpdate($permissions): void
    {
        $this->permission->expects($this->once())
            ->method('httpPut')
            ->with(AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/account_accesses/' . self::FAKE_ACCOUNT_ACCESS_ID . '/permissions/bulk')
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode(['message' => 'Permissions have been updated!'])));

        $response = $this->permission->update(self::FAKE_ACCOUNT_ACCESS_ID, $permissions);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Permissions have been updated!', $responseData['message']);
    }

    public function testInvalidUpdate(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('At least one "permission" object must be provided');

        $emptyPermissions = new Permissions();
        $this->permission->update(self::FAKE_ACCOUNT_ACCESS_ID, $emptyPermissions);
    }

    /**
     * @dataProvider validUpdateDataProvider
     */
    public function testValidGetPayload($permissions, $expectedResult): void
    {
        $this->assertEquals($expectedResult, $permissions->toPayload());
    }

    public function validUpdateDataProvider(): iterable
    {
        // create/update
        yield [
            new Permissions(
                new CreateOrUpdatePermission(1000001, PermissionInterface::TYPE_PROJECT, 10),
            ),
            [
                [
                    "resource_id" => "1000001",
                    "resource_type" => "project",
                    "access_level" => "10"
                ]
            ]
        ];

        // destroy
        yield [
            new Permissions(
                new DestroyPermission(1000009, PermissionInterface::TYPE_PROJECT),
            ),
            [
                [
                    "resource_id" => "1000009",
                    "resource_type" => "project",
                    "_destroy" => true
                ]
            ]
        ];

        // create/update and destroy together
        yield [
            new Permissions(
                new CreateOrUpdatePermission(1000001, PermissionInterface::TYPE_PROJECT, 10),
                new CreateOrUpdatePermission(2000002, PermissionInterface::TYPE_INBOX, 100),
                new DestroyPermission(1000009, PermissionInterface::TYPE_PROJECT),
            ),
            [
                [
                    "resource_id" => "1000001",
                    "resource_type" => "project",
                    "access_level" => "10"
                ],
                [
                    "resource_id" => "2000002",
                    "resource_type" => "inbox",
                    "access_level" => "100"
                ],
                [
                    "resource_id" => "1000009",
                    "resource_type" => "project",
                    "_destroy" => true
                ]
            ]
        ];
    }

    private function getExpectedData()
    {
        return [
            [
                "id" => 4001,
                "name" => "My First Project",
                "type" => "project",
                "access_level" => 1,
                "resources" => [
                    [
                        "id" => 3816,
                        "name" => "My First Inbox",
                        "type" => "inbox",
                        "access_level" => 100,
                        "resources" => [
                        ]
                    ]
                ]
            ],
            [
                "id" => 4002,
                "name" => "My Second Project",
                "type" => "project",
                "access_level" => 1,
                "resources" => [
                    [
                        "id" => 3820,
                        "name" => "My Second Inbox",
                        "type" => "inbox",
                        "access_level" => 100,
                        "resources" => [
                        ]
                    ]
                ]
            ]
        ];
    }
}
