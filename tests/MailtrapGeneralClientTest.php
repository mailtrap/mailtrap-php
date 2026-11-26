<?php

declare(strict_types=1);

namespace Mailtrap\Tests;

use Mailtrap\Api\General\GeneralInterface;
use Mailtrap\MailtrapGeneralClient;

/**
 * @covers MailtrapGeneralClient
 *
 * Class MailtrapGeneralClientTest
 */
class MailtrapGeneralClientTest extends MailtrapClientTestCase
{
    public function getMailtrapClientClassName(): string
    {
        return MailtrapGeneralClient::class;
    }

    public function getLayerInterfaceClassName(): string
    {
        return GeneralInterface::class;
    }

    public static function mapInstancesProvider(): iterable
    {
        foreach (MailtrapGeneralClient::API_MAPPING as $key => $item) {
            yield match ($key) {
                'permissions', 'users', 'contacts', 'emailTemplates', 'templates', 'billing', 'apiTokens', 'emailCampaigns' => [$item, [self::FAKE_ACCOUNT_ID]],
                'organization' => [$item, [self::FAKE_ORGANIZATION_ID]],
                default => [$item],
            };
        }
    }
}
