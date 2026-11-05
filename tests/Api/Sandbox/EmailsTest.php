<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\Sandbox;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Sandbox\Emails;
use Mailtrap\EmailHeader\Template\TemplateUuidHeader;
use Mailtrap\EmailHeader\Template\TemplateVariableHeader;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Mime\MailtrapEmail;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * @covers Emails
 *
 * Class EmailsTest
 */
final class EmailsTest extends MailtrapTestCase
{
    /**
     * @var Emails
     */
    private $email;

    protected function setUp(): void
    {
        parent::setUp();

        $this->email = $this->getMockBuilder(Emails::class)
            ->onlyMethods(['httpPost'])
            ->setConstructorArgs([$this->getConfigMock(), self::FAKE_SANDBOX_ID])
            ->getMock()
        ;
    }

    protected function tearDown(): void
    {
        $this->email = null;

        parent::tearDown();
    }

    public function testValidSendToSandBox(): void
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
            ->addTextHeader('X-Message-Source', 'dev.mydomain.com');

        $this->email
            ->expects($this->once())
            ->method('httpPost')
            ->with(AbstractApi::SENDMAIL_SANDBOX_HOST . '/api/send/' . self::FAKE_SANDBOX_ID, [], [
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

    public function testValidSendTemplateToSandbox(): void
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
            ->with(AbstractApi::SENDMAIL_SANDBOX_HOST . '/api/send/' . self::FAKE_SANDBOX_ID, [], [
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

    public function testValidSendTemplateToSandboxNewEmailWrapper(): void
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
            ])
        ;

        $this->email
            ->expects($this->once())
            ->method('httpPost')
            ->with(AbstractApi::SENDMAIL_SANDBOX_HOST . '/api/send/' . self::FAKE_SANDBOX_ID, [], [
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
                    'int' => 123
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
            ->with(AbstractApi::SENDMAIL_SANDBOX_HOST . '/api/batch/' . self::FAKE_SANDBOX_ID, [], $expectedPayload)
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], json_encode($expectedResponse)));

        $response = $this->email->batchSend($recipientEmails, $baseEmail);
        $responseData = ResponseHelper::toArray($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('responses', $responseData);
        $this->assertCount(2, $responseData['responses']);
    }
}
