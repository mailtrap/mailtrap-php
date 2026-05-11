<?php

declare(strict_types=1);

namespace Mailtrap\Tests;

use Mailtrap\ConfigInterface;
use Mailtrap\HttpClient\HttpClientBuilderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;

/**
 * Class MailtrapTestCase
 */
abstract class MailtrapTestCase extends TestCase
{
    public const DEFAULT_API_KEY = 'some_api_key';
    public const FAKE_ACCOUNT_ID = 10001;
    public const FAKE_ORGANIZATION_ID = 7777;
    public const FAKE_ACCOUNT_ACCESS_ID = 1000001;
    public const FAKE_PROJECT_ID = 2436;
    public const FAKE_INBOX_ID = 4015;
    public const FAKE_MESSAGE_ID = 457;
    public const FAKE_MESSAGE_ATTACHMENT_ID = 67;

    protected function getHttpClientMock(): ClientInterface
    {
        $client = $this->getMockBuilder(ClientInterface::class)
            ->onlyMethods(['sendRequest'])
            ->getMock();

        $client
            ->expects($this->any())
            ->method('sendRequest');

        return $client;
    }

    protected function getConfigMock(): ConfigInterface
    {
        $config = $this->getMockBuilder(ConfigInterface::class)
            ->onlyMethods(['getHttpClientBuilder', 'getApiToken', 'getHost', 'isResponseThrowOnError'])
            ->getMock();

        $config
            ->method('getHttpClientBuilder')
            ->willReturn($this->getHttpClientBuilderMock());

        $config
            ->method('getApiToken')
            ->willReturn(self::DEFAULT_API_KEY);

        $config
            ->method('isResponseThrowOnError')
            ->willReturn(true);

        return $config;
    }

    protected function getHttpClientBuilderMock(): HttpClientBuilderInterface
    {
        $builder = $this->getMockBuilder(HttpClientBuilderInterface::class)
            ->onlyMethods(['getHttpClient'])
            ->disableOriginalConstructor()
            ->getMock();

        $builder
            ->method('getHttpClient')
            ->willReturn($this->getHttpClientMock());

        return $builder;
    }
}
