<?php

declare(strict_types=1);

namespace Mailtrap\Tests;

use Mailtrap\Api\Inbound\InboundInterface;
use Mailtrap\MailtrapInboundClient;

/**
 * @covers \Mailtrap\MailtrapInboundClient
 *
 * Class MailtrapInboundClientTest
 */
class MailtrapInboundClientTest extends MailtrapClientTestCase
{
    public function getMailtrapClientClassName(): string
    {
        return MailtrapInboundClient::class;
    }

    public function getLayerInterfaceClassName(): string
    {
        return InboundInterface::class;
    }

    public function mapInstancesProvider(): iterable
    {
        foreach (MailtrapInboundClient::API_MAPPING as $key => $item) {
            yield match ($key) {
                'inboxes', 'messages', 'threads' => [new $item($this->getConfigMock(), 1)],
                default => [new $item($this->getConfigMock())],
            };
        }
    }
}
