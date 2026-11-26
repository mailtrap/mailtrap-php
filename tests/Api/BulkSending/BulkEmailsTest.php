<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\BulkSending;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\BulkSending\Emails;
use Mailtrap\Tests\Api\AbstractEmailsTestCase;

/**
 * @covers Emails
 *
 * Class BulkEmailsTest
 */
final class BulkEmailsTest extends AbstractEmailsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->email = $this->getMockBuilder(Emails::class)
            ->onlyMethods(['httpPost'])
            ->setConstructorArgs([$this->getConfigMock()])
            ->getMock()
        ;
    }

    protected function tearDown(): void
    {
        $this->email = null;

        parent::tearDown();
    }

    protected function getHost(): string
    {
        return AbstractApi::SENDMAIL_BULK_HOST;
    }
}
