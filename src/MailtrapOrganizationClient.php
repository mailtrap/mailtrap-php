<?php

declare(strict_types=1);

namespace Mailtrap;

/**
 * @method Api\Organization\SubAccount subAccounts(int $organizationId)
 *
 * Class MailtrapOrganizationClient
 */
final class MailtrapOrganizationClient extends AbstractMailtrapClient
{
    public const API_MAPPING = [
        'subAccounts' => Api\Organization\SubAccount::class,
    ];
}
