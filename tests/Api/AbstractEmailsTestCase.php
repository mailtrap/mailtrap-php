<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Sending\Emails;
use Mailtrap\Api\BulkSending\Emails as BulkEmails;
use Mailtrap\EmailHeader\CategoryHeader;
use Mailtrap\EmailHeader\CustomVariableHeader;
use Mailtrap\EmailHeader\Template\TemplateUuidHeader;
use Mailtrap\EmailHeader\Template\TemplateVariableHeader;
use Mailtrap\Exception\HttpClientException;
use Mailtrap\Exception\LogicException;
use Mailtrap\Exception\RuntimeException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Mime\MailtrapEmail;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Class AbstractEmailsTestCase
 */
abstract class AbstractEmailsTestCase extends MailtrapTestCase
{
    /**
     * @var Emails|BulkEmails
     */
    protected $email;

    abstract protected function getHost(): string;

    public function testValidSend(): void
    {
        $expectedData = [
            "success" => true,
            "message_ids" => [
                "0c7fd939-02cf-11ed-88c2-0a58a9feac02"
            ]
        ];

        $email = new Email();
        $email->from(new Address('foo@example.com', 'Ms. Foo Bar'))
            ->replyTo(new Address('reply@example.com'))
            ->to(new Address('bar@example.com', 'Mr. Recipient'))
            ->priority(Email::PRIORITY_HIGH)
            ->bcc('baz@example.com')
            ->subject('Best practices of building HTML emails')
            ->text('Some text')
            ->html('<p>Some text</p>')
        ;
        $email->getHeaders()
            ->addTextHeader('X-Message-Source', 'dev.mydomain.com')
            ->addTextHeader('Test-Unicode-Header', 'Subašić');

        $this->email
            ->expects($this->once())
            ->method('httpPost')
            ->with($this->getHost() . '/api/send', [], [
                'from' => [
                    'email' => 'foo@example.com',
                    'name' => 'Ms. Foo Bar',
                ],
                'to' => [[
                    'email' => 'bar@example.com',
                    'name' => 'Mr. Recipient',
                ]],
                'subject' => 'Best practices of building HTML emails',
                'bcc' => [[
                    'email' => 'baz@example.com'
                ]],
                'text' => 'Some text',
                'html' => '<p>Some text</p>',
                'headers' => [
                    'X-Message-Source' => 'dev.mydomain.com',
                    'Reply-To' => 'reply@example.com',
                    'X-Priority' => '2 (High)',
                    'Test-Unicode-Header' => '=?utf-8?Q?Suba=C5=A1i=C4=87?=', // See RFC 2822, Sect 2.2
                ]
            ])
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedData)));

        $response = $this->email->send($email);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertCount(2, $responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('message_ids', $responseData);
    }

    public function testInValidSend(): void
    {
        $expectedData = [
            "errors" => [
                "'to' address is required",
                "'subject' is required",
            ]
        ];

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage(
            "Bad request. Fix errors listed in response before retrying. Errors: 'to' address is required. 'subject' is required."
        );

        $email = new Email();
        $email->from(new Address('foo@example.com', 'Ms. Foo Bar'))
            ->text('Some text')
            ->html('<p>Some text</p>')
        ;
        $email->getHeaders()
            ->addTextHeader('X-Message-Source', 'dev.mydomain.com');

        $this->email
            ->expects($this->once())
            ->method('httpPost')
            ->with($this->getHost() . '/api/send', [], [
                'from' => [
                    'email' => 'foo@example.com',
                    'name' => 'Ms. Foo Bar',
                ],
                'text' => 'Some text',
                'html' => '<p>Some text</p>',
                'headers' => [
                    'X-Message-Source' => 'dev.mydomain.com'
                ]
            ])
            ->willReturn(new Response(400, ['Content-Type' => 'application/json'], json_encode($expectedData)));

        $this->email->send($email);
    }

    public function testValidSendTemplate(): void
    {
        $expectedData = [
            "success" => true,
            "message_ids" => [
                "0c7fd939-02cf-11ed-88c2-0a58a9feac02"
            ]
        ];

        $email = (new Email())
            ->from(new Address('foo@example.com', 'Ms. Foo Bar'))
            ->to(new Address('bar@example.com', 'Mr. Recipient'))
        ;

        $email->getHeaders()
            ->add(new TemplateUuidHeader('bfa432fd-0000-0000-0000-8493da283a69'))
            ->add(new TemplateVariableHeader('user_name', 'Jon Bush'))
            ->add(new TemplateVariableHeader('unicode_user_name', 'Subašić'))
            ->add(new TemplateVariableHeader('next_step_link', 'https://mailtrap.io/'))
            ->add(new TemplateVariableHeader('get_started_link', 'https://mailtrap.io/'))
            ->add(new TemplateVariableHeader('onboarding_video_link', 'some_video_link'))
            ->add(new TemplateVariableHeader('company', [
                'name' => 'Best Company',
                'address' => 'Its Address',
            ]))
            ->add(new TemplateVariableHeader('products', [
                [
                    'name' => 'Product 1',
                    'price' => 100,
                ],
                [
                    'name' => 'Product 2',
                    'price' => 200,
                ],
            ]))
            ->add(new TemplateVariableHeader('isBool', true))
            ->add(new TemplateVariableHeader('int', 123))
        ;

        $this->email
            ->expects($this->once())
            ->method('httpPost')
            ->with($this->getHost() . '/api/send', [], [
                'from' => [
                    'email' => 'foo@example.com',
                    'name' => 'Ms. Foo Bar',
                ],
                'to' => [[
                    'email' => 'bar@example.com',
                    'name' => 'Mr. Recipient',
                ]],
                'template_uuid' => 'bfa432fd-0000-0000-0000-8493da283a69',
                'template_variables' => [
                    'user_name' => 'Jon Bush',
                    'unicode_user_name' => 'Subašić', // should not be encoded as it is not a real header
                    'next_step_link' => 'https://mailtrap.io/',
                    'get_started_link' => 'https://mailtrap.io/',
                    'onboarding_video_link' => 'some_video_link',
                    'company' => [
                        'name' => 'Best Company',
                        'address' => 'Its Address',
                    ],
                    'products' => [
                        [
                            'name' => 'Product 1',
                            'price' => 100,
                        ],
                        [
                            'name' => 'Product 2',
                            'price' => 200,
                        ],
                    ],
                    'isBool' => true,
                    'int' => 123,
                ]
            ])
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedData)));

        $response = $this->email->send($email);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertCount(2, $responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('message_ids', $responseData);
    }

    public function testValidSendTemplateNewEmailWrapper(): void
    {
        $expectedData = [
            "success" => true,
            "message_ids" => [
                "0c7fd939-02cf-11ed-88c2-0a58a9feac02"
            ]
        ];

        $email = (new MailtrapEmail())
            ->from(new Address('foo@example.com', 'Ms. Foo Bar'))
            ->to(new Address('bar@example.com', 'Mr. Recipient'))
            ->templateUuid('bfa432fd-0000-0000-0000-8493da283a69')
            ->templateVariables([
                'user_name' => 'Jon Bush',
                'unicode_user_name' => 'Subašić',
                'next_step_link' => 'https://mailtrap.io/',
                'get_started_link' => 'https://mailtrap.io/',
                'onboarding_video_link' => 'some_video_link',
                'company' => [
                    'name' => 'Best Company',
                    'address' => 'Its Address',
                ],
                'products' => [
                    [
                        'name' => 'Product 1',
                        'price' => 100,
                    ],
                    [
                        'name' => 'Product 2',
                        'price' => 200,
                    ],
                ],
                'isBool' => true,
                'int' => 123,
            ])
        ;

        $this->email
            ->expects($this->once())
            ->method('httpPost')
            ->with($this->getHost() . '/api/send', [], [
                'from' => [
                    'email' => 'foo@example.com',
                    'name' => 'Ms. Foo Bar',
                ],
                'to' => [[
                    'email' => 'bar@example.com',
                    'name' => 'Mr. Recipient',
                ]],
                'template_uuid' => 'bfa432fd-0000-0000-0000-8493da283a69',
                'template_variables' => [
                    'user_name' => 'Jon Bush',
                    'unicode_user_name' => 'Subašić', // should not be encoded as it is not a real header
                    'next_step_link' => 'https://mailtrap.io/',
                    'get_started_link' => 'https://mailtrap.io/',
                    'onboarding_video_link' => 'some_video_link',
                    'company' => [
                        'name' => 'Best Company',
                        'address' => 'Its Address',
                    ],
                    'products' => [
                        [
                            'name' => 'Product 1',
                            'price' => 100,
                        ],
                        [
                            'name' => 'Product 2',
                            'price' => 200,
                        ],
                    ],
                    'isBool' => true,
                    'int' => 123,
                ]
            ])
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedData)));

        $response = $this->email->send($email);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertCount(2, $responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('message_ids', $responseData);
    }

    public function testAttachments(): void
    {
        $email = (new Email())
            ->from(new Address('foo@example.com', 'Ms. Foo Bar'))
            ->attach('fake_body', 'fakeFile.jpg', 'image/jpg')
        ;

        $method = new \ReflectionMethod(Emails::class, 'getPayload');
        $method->setAccessible(true);
        $payload = $method->invoke(new Emails($this->getConfigMock()), $email);

        $this->assertArrayHasKey('attachments', $payload);
        $this->assertArrayHasKey('content', $payload['attachments'][0]);
        $this->assertArrayHasKey('type', $payload['attachments'][0]);
        $this->assertArrayHasKey('filename', $payload['attachments'][0]);
        $this->assertEquals('ZmFrZV9ib2R5', $payload['attachments'][0]['content']);
        $this->assertEquals('image/jpg', $payload['attachments'][0]['type']);
        $this->assertEquals('fakeFile.jpg', $payload['attachments'][0]['filename']);
    }

    /**
     * @dataProvider validHeadersDataProvider
     */
    #[DataProvider('validHeadersDataProvider')]
    public function testHeaders($name, $value): void
    {
        $email = (new Email())->from(new Address('foo@example.com', 'Ms. Foo Bar'));
        $email->getHeaders()->addTextHeader($name, $value);

        $method = new \ReflectionMethod(Emails::class, 'getPayload');
        $method->setAccessible(true);
        $payload = $method->invoke(new Emails($this->getConfigMock()), $email);

        $this->assertArrayHasKey('headers', $payload);
        $this->assertArrayHasKey($name, $payload['headers']);
        $this->assertEquals($value, $payload['headers'][$name]);
    }

    /**
     * @dataProvider validCustomVariablesDataProvider
     */
    #[DataProvider('validCustomVariablesDataProvider')]
    public function testCustomVariables($name, $value): void
    {
        $email = (new Email())->from(new Address('foo@example.com', 'Ms. Foo Bar'));
        $email->getHeaders()
            ->add(new CustomVariableHeader($name, $value));

        $method = new \ReflectionMethod(Emails::class, 'getPayload');
        $method->setAccessible(true);
        $payload = $method->invoke(new Emails($this->getConfigMock()), $email);

        $this->assertArrayHasKey(CustomVariableHeader::VAR_NAME, $payload);
        $this->assertArrayHasKey($name, $payload[CustomVariableHeader::VAR_NAME]);
        $this->assertEquals($value, $payload[CustomVariableHeader::VAR_NAME][$name]);
    }

    /**
     * @dataProvider validCustomVariablesDataProvider
     */
    #[DataProvider('validCustomVariablesDataProvider')]
    public function testCustomVariablesNewEmailWrapper($name, $value): void
    {
        $email = (new MailtrapEmail())
            ->from(new Address('foo@example.com', 'Ms. Foo Bar'))
            ->customVariable($name, $value)
        ;

        $method = new \ReflectionMethod(Emails::class, 'getPayload');
        $method->setAccessible(true);
        $payload = $method->invoke(new Emails($this->getConfigMock()), $email);

        $this->assertArrayHasKey(CustomVariableHeader::VAR_NAME, $payload);
        $this->assertArrayHasKey($name, $payload[CustomVariableHeader::VAR_NAME]);
        $this->assertEquals($value, $payload[CustomVariableHeader::VAR_NAME][$name]);
    }

    /**
     * @dataProvider validEmailCategoryDataProvider
     */
    #[DataProvider('validEmailCategoryDataProvider')]
    public function testEmailCategory($value): void
    {
        $email = (new Email())->from(new Address('foo@example.com', 'Ms. Foo Bar'));
        $email->getHeaders()
            ->add(new CategoryHeader($value));

        $method = new \ReflectionMethod(Emails::class, 'getPayload');
        $method->setAccessible(true);
        $payload = $method->invoke(new Emails($this->getConfigMock()), $email);

        $this->assertArrayHasKey(CategoryHeader::VAR_NAME, $payload);
        $this->assertEquals($value, $payload[CategoryHeader::VAR_NAME]);
    }

    /**
     * @dataProvider validEmailCategoryDataProvider
     */
    #[DataProvider('validEmailCategoryDataProvider')]
    public function testEmailCategoryNewEmailWrapper($value): void
    {
        $email = (new MailtrapEmail())
            ->from(new Address('foo@example.com', 'Ms. Foo Bar'))
            ->category($value)
        ;

        $method = new \ReflectionMethod(Emails::class, 'getPayload');
        $method->setAccessible(true);
        $payload = $method->invoke(new Emails($this->getConfigMock()), $email);

        $this->assertArrayHasKey(CategoryHeader::VAR_NAME, $payload);
        $this->assertEquals($value, $payload[CategoryHeader::VAR_NAME]);
    }

    public function testValidCountOfEmailCategoryNewEmailWrapper(): void
    {
        $email = (new MailtrapEmail())
            ->from(new Address('foo@example.com', 'Ms. Foo Bar'))
            ->category('category 1')
            ->category('category 2') // will be overridden
        ;

        $method = new \ReflectionMethod(Emails::class, 'getPayload');
        $method->setAccessible(true);
        $payload = $method->invoke(new Emails($this->getConfigMock()), $email);

        $this->assertArrayHasKey(CategoryHeader::VAR_NAME, $payload);
        $this->assertEquals('category 2', $payload[CategoryHeader::VAR_NAME]);
    }

    public function testInvalidCountOfEmailCategory(): void
    {
        $email = (new Email())->from(new Address('foo@example.com', 'Ms. Foo Bar'));
        $email->getHeaders()
            ->add(new CategoryHeader('category 1'))
            ->add(new CategoryHeader('category 2'))
        ;

        $this->expectExceptionObject(
            new RuntimeException(
                sprintf('Too many "%s" instances present in the email headers. Mailtrap does not accept more than 1 category in the email.', CategoryHeader::class)
            )
        );

        $method = new \ReflectionMethod(Emails::class, 'getPayload');
        $method->setAccessible(true);
        $method->invoke(new Emails($this->getConfigMock()), $email);
    }

    /**
     * @dataProvider validTemplateUuidDataProvider
     */
    #[DataProvider('validTemplateUuidDataProvider')]
    public function testTemplateUuid($value): void
    {
        $email = (new Email())->from(new Address('foo@example.com', 'Ms. Foo Bar'));
        $email->getHeaders()
            ->add(new TemplateUuidHeader($value));

        $method = new \ReflectionMethod(Emails::class, 'getPayload');
        $method->setAccessible(true);
        $payload = $method->invoke(new Emails($this->getConfigMock()), $email);

        $this->assertArrayHasKey(TemplateUuidHeader::VAR_NAME, $payload);
        $this->assertEquals($value, $payload[TemplateUuidHeader::VAR_NAME]);
    }

    /**
     * @dataProvider validTemplateUuidDataProvider
     */
    #[DataProvider('validTemplateUuidDataProvider')]
    public function testTemplateUuidNewEmailWrapper($value): void
    {
        $email = (new MailtrapEmail())
            ->from(new Address('foo@example.com', 'Ms. Foo Bar'))
            ->templateUuid($value)
        ;

        $method = new \ReflectionMethod(Emails::class, 'getPayload');
        $method->setAccessible(true);
        $payload = $method->invoke(new Emails($this->getConfigMock()), $email);

        $this->assertArrayHasKey(TemplateUuidHeader::VAR_NAME, $payload);
        $this->assertEquals($value, $payload[TemplateUuidHeader::VAR_NAME]);
    }

    public function testValidCountOfTemplateUuidNewEmailWrapper(): void
    {
        $email = (new MailtrapEmail())
            ->from(new Address('foo@example.com', 'Ms. Foo Bar'))
            ->templateUuid('11111111-0000-0000-0000-8493da283a69')
            ->templateUuid('22222222-0000-0000-0000-8493da283a69') // will be overridden
        ;

        $method = new \ReflectionMethod(Emails::class, 'getPayload');
        $method->setAccessible(true);
        $payload = $method->invoke(new Emails($this->getConfigMock()), $email);

        $this->assertArrayHasKey(TemplateUuidHeader::VAR_NAME, $payload);
        $this->assertEquals('22222222-0000-0000-0000-8493da283a69', $payload[TemplateUuidHeader::VAR_NAME]);
    }

    public function testInvalidCountOfTemplateUuid(): void
    {
        $email = (new Email())->from(new Address('foo@example.com', 'Ms. Foo Bar'));
        $email->getHeaders()
            ->add(new TemplateUuidHeader('11111111-0000-0000-0000-8493da283a69'))
            ->add(new TemplateUuidHeader('22222222-0000-0000-0000-8493da283a69'))
        ;

        $this->expectExceptionObject(
            new RuntimeException(
                sprintf('Too many "%s" instances present in the email headers. Mailtrap does not accept more than 1 template UUID in the email.', TemplateUuidHeader::class)
            )
        );

        $method = new \ReflectionMethod(Emails::class, 'getPayload');
        $method->setAccessible(true);
        $method->invoke(new Emails($this->getConfigMock()), $email);
    }

    /**
     * @dataProvider validTemplateVariablesDataProvider
     */
    #[DataProvider('validTemplateVariablesDataProvider')]
    public function testTemplateVariables($name, $value): void
    {
        $email = (new Email())->from(new Address('foo@example.com', 'Ms. Foo Bar'));
        $email->getHeaders()
            ->add(new TemplateVariableHeader($name, $value));

        $method = new \ReflectionMethod(Emails::class, 'getPayload');
        $method->setAccessible(true);
        $payload = $method->invoke(new Emails($this->getConfigMock()), $email);

        $this->assertArrayHasKey(TemplateVariableHeader::VAR_NAME, $payload);
        $this->assertArrayHasKey($name, $payload[TemplateVariableHeader::VAR_NAME]);
        $this->assertEquals($value, $payload[TemplateVariableHeader::VAR_NAME][$name]);
    }

    /**
     * @dataProvider validTemplateVariablesDataProvider
     */
    #[DataProvider('validTemplateVariablesDataProvider')]
    public function testTemplateVariablesNewEmailWrapper($name, $value): void
    {
        $email = (new MailtrapEmail())
            ->from(new Address('foo@example.com', 'Ms. Foo Bar'))
            ->templateVariable($name, $value)
        ;

        $method = new \ReflectionMethod(Emails::class, 'getPayload');
        $method->setAccessible(true);
        $payload = $method->invoke(new Emails($this->getConfigMock()), $email);

        $this->assertArrayHasKey(TemplateVariableHeader::VAR_NAME, $payload);
        $this->assertArrayHasKey($name, $payload[TemplateVariableHeader::VAR_NAME]);
        $this->assertEquals($value, $payload[TemplateVariableHeader::VAR_NAME][$name]);
    }

    public function testBatchSend(): void
    {
        $baseEmail = (new Email())
            ->from(new Address('foo@example.com', 'Ms. Foo Bar'))
            ->subject('Batch Email Subject')
            ->text('Batch email text')
            ->html('<p>Batch email text</p>');

        $recipientEmails = [
            (new Email())->to(new Address('recipient1@example.com', 'Recipient 1')),
            (new Email())->to(new Address('recipient2@example.com', 'Recipient 2')),
        ];

        $expectedPayload = [
            'base' => [
                'from' => [
                    'email' => 'foo@example.com',
                    'name' => 'Ms. Foo Bar',
                ],
                'subject' => 'Batch Email Subject',
                'text' => 'Batch email text',
                'html' => '<p>Batch email text</p>',
            ],
            'requests' => [
                [
                    'to' => [[
                        'email' => 'recipient1@example.com',
                        'name' => 'Recipient 1',
                    ]],
                ],
                [
                    'to' => [[
                        'email' => 'recipient2@example.com',
                        'name' => 'Recipient 2',
                    ]],
                ],
            ],
        ];

        $expectedResponse = [
            'success' => true,
            'responses' => [
                [
                    'success' => true,
                    'message_ids' => [
                        '53f764a0-4dca-11f0-0000-f185d7639148',
                    ],
                ],
                [
                    'success' => true,
                    'message_ids' => [
                        '53f764a0-4dca-11f0-0001-f185d7639148',
                    ],
                ],
            ],
        ];

        $this->email
            ->expects($this->once())
            ->method('httpPost')
            ->with($this->getHost() . '/api/batch', [], $expectedPayload)
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedResponse)));

        $response = $this->email->batchSend($recipientEmails, $baseEmail);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('responses', $responseData);
        $this->assertCount(2, $responseData['responses']);
    }

    public function testBatchSendWithoutBaseParam(): void
    {
        $recipientEmails = [
            (new Email())
                ->from(new Address('sender@example.com', 'Sender Name'))
                ->to(new Address('recipient1@example.com', 'Recipient 1'))
                ->subject('Test Subject 1')
                ->text('Test email body 1'),
            (new Email())
                ->from(new Address('sender@example.com', 'Sender Name'))
                ->to(new Address('recipient2@example.com', 'Recipient 2'))
                ->subject('Test Subject 2')
                ->html('<p>Test email body 2</p>'),
        ];

        $expectedPayload = [
            'requests' => [
                [
                    'from' => [
                        'email' => 'sender@example.com',
                        'name' => 'Sender Name',
                    ],
                    'to' => [[
                        'email' => 'recipient1@example.com',
                        'name' => 'Recipient 1',
                    ]],
                    'subject' => 'Test Subject 1',
                    'text' => 'Test email body 1',
                ],
                [
                    'from' => [
                        'email' => 'sender@example.com',
                        'name' => 'Sender Name',
                    ],
                    'to' => [[
                        'email' => 'recipient2@example.com',
                        'name' => 'Recipient 2',
                    ]],
                    'subject' => 'Test Subject 2',
                    'html' => '<p>Test email body 2</p>',
                ],
            ],
        ];

        $expectedResponse = [
            'success' => true,
            'responses' => [
                [
                    'success' => true,
                    'message_ids' => [
                        '53f764a0-4dca-11f0-0000-f185d7639148',
                    ],
                ],
                [
                    'success' => true,
                    'message_ids' => [
                        '53f764a0-4dca-11f0-0001-f185d7639148',
                    ],
                ],
            ],
        ];

        $this->email
            ->expects($this->once())
            ->method('httpPost')
            ->with($this->getHost() . '/api/batch', [], $expectedPayload)
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedResponse)));

        $response = $this->email->batchSend($recipientEmails);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('responses', $responseData);
        $this->assertCount(2, $responseData['responses']);
    }

    public function testBatchSendInvalidWithoutBaseAndRequiredFields(): void
    {
        $recipientEmails = [
            (new Email())->to(new Address('recipient1@example.com', 'Recipient 1')),
            (new Email())->to(new Address('recipient2@example.com', 'Recipient 2')),
        ];

        $expectedPayload = [
            'requests' => [
                [
                    'to' => [[
                        'email' => 'recipient1@example.com',
                        'name' => 'Recipient 1',
                    ]],
                ],
                [
                    'to' => [[
                        'email' => 'recipient2@example.com',
                        'name' => 'Recipient 2',
                    ]],
                ],
            ],
        ];

        $expectedResponse = [
            'success' => true,
            'responses' => [
                [
                    'success' => false,
                    'errors' => [
                        "'from' is required",
                        "'subject' is required",
                        "must specify either text or html body",
                    ],
                ],
                [
                    'success' => false,
                    'errors' => [
                        "'from' is required",
                        "'subject' is required",
                        "must specify either text or html body",
                    ],
                ],
            ],
        ];

        $this->email
            ->expects($this->once())
            ->method('httpPost')
            ->with($this->getHost() . '/api/batch', [], $expectedPayload)
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedResponse)));

        $response = $this->email->batchSend($recipientEmails);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('responses', $responseData);
        $this->assertCount(2, $responseData['responses']);
        foreach ($responseData['responses'] as $recipientResponse) {
            $this->assertFalse($recipientResponse['success']);
            $this->assertArrayHasKey('errors', $recipientResponse);
            $this->assertContains("'from' is required", $recipientResponse['errors']);
            $this->assertContains("'subject' is required", $recipientResponse['errors']);
            $this->assertContains("must specify either text or html body", $recipientResponse['errors']);
        }
    }

    public function testBatchSendWithTemplateId(): void
    {
        $baseEmail = (new MailtrapEmail())
            ->from(new Address('sender@example.com', 'Sender Name'))
            ->templateUuid('bfa432fd-0000-413d-9d6e-8493da283a69')
            ->templateVariables([
                'user_name' => 'John Doe',
                'next_step_link' => 'https://example.com/next-step',
                'company' => [
                    'name' => 'Example Company',
                    'address' => '123 Example Street',
                ],
            ]);

        $recipientEmails = [
            (new MailtrapEmail())
                ->to(new Address('recipient1@example.com', 'Recipient One'))
                ->templateVariables([
                    'user_name' => 'Custom User 1',
                ]),
            (new MailtrapEmail())
                ->to(new Address('recipient2@example.com', 'Recipient Two')),
        ];

        $expectedPayload = [
            'base' => [
                'from' => [
                    'email' => 'sender@example.com',
                    'name' => 'Sender Name',
                ],
                'template_uuid' => 'bfa432fd-0000-413d-9d6e-8493da283a69',
                'template_variables' => [
                    'user_name' => 'John Doe',
                    'next_step_link' => 'https://example.com/next-step',
                    'company' => [
                        'name' => 'Example Company',
                        'address' => '123 Example Street',
                    ],
                ],
            ],
            'requests' => [
                [
                    'to' => [[
                        'email' => 'recipient1@example.com',
                        'name' => 'Recipient One',
                    ]],
                    'template_variables' => [
                        'user_name' => 'Custom User 1',
                    ],
                ],
                [
                    'to' => [[
                        'email' => 'recipient2@example.com',
                        'name' => 'Recipient Two',
                    ]],
                ],
            ],
        ];

        $expectedResponse = [
            'success' => true,
            'responses' => [
                [
                    'success' => true,
                    'message_ids' => [
                        '53f764a0-4dca-11f0-0000-f185d7639148',
                    ],
                ],
                [
                    'success' => true,
                    'message_ids' => [
                        '53f764a0-4dca-11f0-0001-f185d7639148',
                    ],
                ],
            ],
        ];

        $this->email
            ->expects($this->once())
            ->method('httpPost')
            ->with($this->getHost() . '/api/batch', [], $expectedPayload)
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedResponse)));

        $response = $this->email->batchSend($recipientEmails, $baseEmail);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('responses', $responseData);
        $this->assertCount(2, $responseData['responses']);
    }

    public function testBatchSendWithTemplateUuidFailsDueToSubjectInRecipientEmails(): void
    {
        $baseEmail = (new MailtrapEmail())
            ->from(new Address('sender@example.com', 'Sender Name'))
            ->templateUuid('bfa432fd-0000-413d-9d6e-8493da283a69')
            ->templateVariables([
                'user_name' => 'John Doe',
                'next_step_link' => 'https://example.com/next-step',
                'company' => [
                    'name' => 'Example Company',
                    'address' => '123 Example Street',
                ],
            ]);

        $recipientEmails = [
            (new MailtrapEmail())
                ->to(new Address('recipient1@example.com', 'Recipient One'))
                ->subject('Invalid Subject'), // Invalid field
            (new MailtrapEmail())
                ->to(new Address('recipient2@example.com', 'Recipient Two'))
                ->subject('Invalid Subject'), // Invalid field
        ];

        $expectedPayload = [
            'base' => [
                'from' => [
                    'email' => 'sender@example.com',
                    'name' => 'Sender Name',
                ],
                'template_uuid' => 'bfa432fd-0000-413d-9d6e-8493da283a69',
                'template_variables' => [
                    'user_name' => 'John Doe',
                    'next_step_link' => 'https://example.com/next-step',
                    'company' => [
                        'name' => 'Example Company',
                        'address' => '123 Example Street',
                    ],
                ],
            ],
            'requests' => [
                [
                    'to' => [[
                        'email' => 'recipient1@example.com',
                        'name' => 'Recipient One',
                    ]],
                    'subject' => 'Invalid Subject',
                ],
                [
                    'to' => [[
                        'email' => 'recipient2@example.com',
                        'name' => 'Recipient Two',
                    ]],
                    'subject' => 'Invalid Subject',
                ],
            ],
        ];

        $expectedResponse = [
            'success' => true,
            'responses' => [
                [
                    'success' => false,
                    'errors' => [
                        "'subject' is not allowed with 'template_uuid'",
                    ],
                ],
                [
                    'success' => false,
                    'errors' => [
                        "'subject' is not allowed with 'template_uuid'",
                    ],
                ],
            ],
        ];

        $this->email
            ->expects($this->once())
            ->method('httpPost')
            ->with($this->getHost() . '/api/batch', [], $expectedPayload)
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedResponse)));

        $response = $this->email->batchSend($recipientEmails, $baseEmail);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('responses', $responseData);
        $this->assertCount(2, $responseData['responses']);
        foreach ($responseData['responses'] as $recipientResponse) {
            $this->assertFalse($recipientResponse['success']);
            $this->assertArrayHasKey('errors', $recipientResponse);
            $this->assertContains("'subject' is not allowed with 'template_uuid'", $recipientResponse['errors']);
        }
    }

    public function testBatchSendThrowsLogicExceptionForInvalidBaseEmail(): void
    {
        $baseEmail = (new Email())
            ->from(new Address('foo@example.com', 'Ms. Foo Bar'))
            ->to(new Address('recipient@example.com', 'Recipient')) // Invalid field
            ->subject('Batch Email Subject')
            ->text('Batch email text')
            ->html('<p>Batch email text</p>');

        $recipientEmails = [
            (new Email())->to(new Address('recipient1@example.com', 'Recipient 1')),
        ];

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            "Batch base email does not support 'to', 'cc', or 'bcc' fields. Please use individual batch email requests to specify recipients."
        );

        $this->email->batchSend($recipientEmails, $baseEmail);
    }

    //<editor-fold desc="Data Providers">

    public static function validHeadersDataProvider(): array
    {
        return [
            ['X-Message-Source', 'dev.mydomain.com'],
            ['X-Mailer', 'Mailtrap PHP Client'],
        ];
    }

    public static function validCustomVariablesDataProvider(): array
    {
        return [
            ['user_id', '45982'],
            ['batch_id', 'PSJ-12'],
            ['date', '2025'],
            ['from', 'Some name'],
            ['sender', 'Sender info'],
            ['reply-to', 'Reply to info'],
            ['to', 'To info'],
            ['cc', 'Cc info'],
            ['bcc', 'Bcc info'],
            ['message-id', 'Message ID info'],
            ['in-reply-to', 'In reply to info'],
            ['references', 'References info'],
            ['return-path', 'Return path info'],
        ];
    }

    public static function validEmailCategoryDataProvider(): array
    {
        return [
            ['Integration Test'],
            ['Some other category'],
        ];
    }

    public static function validTemplateUuidDataProvider(): array
    {
        return [
            ['11111111-0000-0000-0000-8493da283a69'],
            ['22222222-0000-0000-0000-8493da283a69'],
        ];
    }

    public static function validTemplateVariablesDataProvider(): array
    {
        return [
            ['user_name', 'Jon Bush'],
            ['next_step_link', 'https://mailtrap.io/'],
            ['onboarding_video_link', 'some_video_link'],
            ['company', [
                'name' => 'Best Company',
                'address' => 'Its Address',
            ]],
            ['products', [
                [
                    'name' => 'Product 1',
                    'price' => 100,
                ],
                [
                    'name' => 'Product 2',
                    'price' => 200,
                ],
            ]],
            ['isBool', true],
            ['int', 123],
            ['date', '2025'],
            ['from', 'Some name'],
            ['sender', 'Sender info'],
            ['reply-to', 'Reply to info'],
            ['to', 'To info'],
            ['cc', 'Cc info'],
            ['bcc', 'Bcc info'],
            ['message-id', 'Message ID info'],
            ['in-reply-to', 'In reply to info'],
            ['references', 'References info'],
            ['return-path', 'Return path info'],
        ];
    }

    //</editor-fold>
}
