<?php

declare(strict_types=1);

namespace Mailtrap;

/**
 * @method  Api\Sending\Emails         emails()
 * @method  Api\Sending\Suppression    suppressions(int $accountId)
 * @method  Api\Sending\TrackingOptOut trackingOptOuts()
 * @method  Api\Sending\Domain         domains(int $accountId)
 * @method  Api\Sending\CompanyInfo    companyInfo(int $domainId)
 * @method  Api\Sending\Stats          stats(int $accountId)
 * @method  Api\Sending\EmailLogs      emailLogs(int $accountId)
 * @method  Api\Sending\Webhook        webhooks(int $accountId)
 *
 * Class MailtrapSendingClient
 */
final class MailtrapSendingClient extends AbstractMailtrapClient implements EmailsSendMailtrapClientInterface
{
    public const API_MAPPING = [
        'emails' => Api\Sending\Emails::class,
        'suppressions' => Api\Sending\Suppression::class,
        'trackingOptOuts' => Api\Sending\TrackingOptOut::class,
        'domains' => Api\Sending\Domain::class,
        'companyInfo' => Api\Sending\CompanyInfo::class,
        'stats' => Api\Sending\Stats::class,
        'emailLogs' => Api\Sending\EmailLogs::class,
        'webhooks' => Api\Sending\Webhook::class,
    ];
}
