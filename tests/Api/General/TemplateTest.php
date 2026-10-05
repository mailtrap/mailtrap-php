<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\General;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\General\Template;
use Mailtrap\DTO\Request\Template\CreateTemplate;
use Mailtrap\DTO\Request\Template\UpdateTemplate;
use Mailtrap\Exception\HttpClientException;
use Mailtrap\Exception\RuntimeException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers \Mailtrap\Api\General\Template
 *
 * Class TemplateTest
 */
class TemplateTest extends MailtrapTestCase
{
    private const BASE_PATH = AbstractApi::DEFAULT_HOST . '/api/accounts/' . self::FAKE_ACCOUNT_ID . '/templates';

    private ?Template $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->template = $this->getMockBuilder(Template::class)
            ->onlyMethods(['httpGet', 'httpPost', 'httpPatch', 'httpDelete'])
            ->setConstructorArgs([$this->getConfigMock(), self::FAKE_ACCOUNT_ID])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->template = null;
        parent::tearDown();
    }

    public function testGetTemplates(): void
    {
        $this->template->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_PATH, [])
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode([
                        'data' => [$this->getExpectedTemplateResponse()],
                        'pagination' => $this->getExpectedPagination(),
                    ])
                )
            );

        $response = $this->template->getTemplates();
        $responseData = ResponseHelper::toArray($response);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('pagination', $responseData);
        $this->assertCount(1, $responseData['data']);
        $this->assertEquals(4567, $responseData['data'][0]['id']);
        $this->assertEquals(1, $responseData['pagination']['token']);
        $this->assertEquals(2, $responseData['pagination']['next_token']);
    }

    public function testGetTemplatesWithFilters(): void
    {
        $this->template->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_PATH, ['per_page' => 25, 'token' => 2])
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode([
                        'data' => [$this->getExpectedTemplateResponse()],
                        'pagination' => $this->getExpectedPagination(),
                    ])
                )
            );

        $response = $this->template->getTemplates(perPage: 25, token: 2);
        $responseData = ResponseHelper::toArray($response);

        $this->assertCount(1, $responseData['data']);
    }

    public function testGetTemplate(): void
    {
        $templateId = 4567;

        $this->template->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_PATH . '/' . $templateId)
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(['data' => $this->getExpectedTemplateResponse()])
                )
            );

        $response = $this->template->getTemplate($templateId);
        $responseData = ResponseHelper::toArray($response);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals($templateId, $responseData['data']['id']);
        $this->assertEquals('Welcome Email', $responseData['data']['name']);
    }

    public function testGetTemplateFailsWithNotFoundError(): void
    {
        $templateId = 999;

        $this->template->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_PATH . '/' . $templateId)
            ->willReturn(
                new Response(404, ['Content-Type' => 'application/json'], json_encode(['error' => 'Not Found']))
            );

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('The requested entity has not been found. Errors: Not Found.');

        $this->template->getTemplate($templateId);
    }

    public function testCreateTemplate(): void
    {
        $createDto = $this->getCreateTemplateDTO();

        $this->template->expects($this->once())
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
                    json_encode(['data' => $this->getExpectedTemplateResponse()])
                )
            );

        $response = $this->template->createTemplate($createDto);
        $responseData = ResponseHelper::toArray($response);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals('Welcome Email', $responseData['data']['name']);
    }

    public function testCreateTemplateSerializesFlatBody(): void
    {
        $this->assertEquals(
            [
                'name' => 'Welcome Email',
                'subject' => 'Welcome to our service!',
                'category' => 'Transactional',
                'body_html' => '<div>Welcome to our service!</div>',
                'body_text' => 'Welcome to our service!',
            ],
            $this->getCreateTemplateDTO()->toArray()
        );
    }

    public function testCreateTemplateOmitsUnsetBodies(): void
    {
        $payload = (new CreateTemplate(name: 'Welcome Email', subject: 'Hi', category: 'Transactional'))->toArray();

        $this->assertEquals(['name' => 'Welcome Email', 'subject' => 'Hi', 'category' => 'Transactional'], $payload);
    }

    public function testCreateTemplateFailsWithValidationErrors(): void
    {
        $invalidDto = new CreateTemplate(name: '', subject: 'Hi', category: 'Transactional');

        $this->template->expects($this->once())
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
                    json_encode(['errors' => ['name' => ['can\'t be blank']]])
                )
            );

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('Errors: name -> can\'t be blank.');

        $this->template->createTemplate($invalidDto);
    }

    public function testUpdateTemplate(): void
    {
        $templateId = 4567;
        $updateDto = new UpdateTemplate(name: 'Updated Welcome Email', bodyHtml: '<div>Updated</div>');

        $this->template->expects($this->once())
            ->method('httpPatch')
            ->with(
                self::BASE_PATH . '/' . $templateId,
                [],
                $updateDto->toArray()
            )
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode(['data' => $this->getExpectedTemplateResponse()])
                )
            );

        $response = $this->template->updateTemplate($templateId, $updateDto);
        $responseData = ResponseHelper::toArray($response);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals($templateId, $responseData['data']['id']);
    }

    public function testUpdateTemplateSerializesProvidedAttributesOnly(): void
    {
        $payload = (new UpdateTemplate(name: 'Updated Welcome Email', bodyHtml: '<div>Updated</div>'))->toArray();

        $this->assertEquals(['name' => 'Updated Welcome Email', 'body_html' => '<div>Updated</div>'], $payload);
    }

    public function testUpdateTemplateRejectsEmptyPayload(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('At least one attribute must be provided to update a template.');

        (new UpdateTemplate())->toArray();
    }

    public function testDeleteTemplate(): void
    {
        $templateId = 4567;

        $this->template->expects($this->once())
            ->method('httpDelete')
            ->with(self::BASE_PATH . '/' . $templateId)
            ->willReturn(new Response(204));

        $response = $this->template->deleteTemplate($templateId);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getBody()->__toString());
    }

    private function getCreateTemplateDTO(): CreateTemplate
    {
        return new CreateTemplate(
            name: 'Welcome Email',
            subject: 'Welcome to our service!',
            category: 'Transactional',
            bodyHtml: '<div>Welcome to our service!</div>',
            bodyText: 'Welcome to our service!',
        );
    }

    private function getExpectedTemplateResponse(): array
    {
        return [
            'id' => 4567,
            'uuid' => 'bfa432fd-0000-0000-0000-8493da283a69',
            'name' => 'Welcome Email',
            'category' => 'Transactional',
            'subject' => 'Welcome to our service!',
            'body_html' => '<div>Welcome to our service!</div>',
            'body_text' => 'Welcome to our service!',
            'created_at' => '2026-05-01T10:15:00.000Z',
            'updated_at' => '2026-05-02T09:00:00.000Z',
        ];
    }

    private function getExpectedPagination(): array
    {
        return [
            'token' => 1,
            'prev_token' => null,
            'next_token' => 2,
            'first_url' => 'https://mailtrap.io/api/templates?per_page=50&token=1',
            'prev_url' => null,
            'current_url' => 'https://mailtrap.io/api/templates?per_page=50&token=1',
            'next_url' => 'https://mailtrap.io/api/templates?per_page=50&token=2',
        ];
    }
}
