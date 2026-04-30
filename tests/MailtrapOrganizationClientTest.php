<?php

declare(strict_types=1);

namespace Mailtrap\Tests;

use Mailtrap\Api\Organization\OrganizationInterface;
use Mailtrap\MailtrapOrganizationClient;

/**
 * @covers MailtrapOrganizationClient
 *
 * Class MailtrapOrganizationClientTest
 */
class MailtrapOrganizationClientTest extends MailtrapClientTestCase
{
    private const FAKE_ORGANIZATION_ID = 7777;

    public function getMailtrapClientClassName(): string
    {
        return MailtrapOrganizationClient::class;
    }

    public function getLayerInterfaceClassName(): string
    {
        return OrganizationInterface::class;
    }

    public function mapInstancesProvider(): iterable
    {
        foreach (MailtrapOrganizationClient::API_MAPPING as $item) {
            yield [new $item($this->getConfigMock(), self::FAKE_ORGANIZATION_ID)];
        }
    }
}
