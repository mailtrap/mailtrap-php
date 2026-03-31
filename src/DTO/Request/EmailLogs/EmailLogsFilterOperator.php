<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\EmailLogs;

/**
 * All supported operators for Email Logs list filters.
 * Use with FilterCriterion::withValue() or ::withoutValue().
 *
 * Operator usage by filter field:
 * - to, from, email_service_provider_response, recipient_mx: CI_* (case-insensitive string)
 * - subject: CI_* or EMPTY / NOT_EMPTY (use withoutValue for empty/not_empty)
 * - status: EQUAL, NOT_EQUAL — value: EmailLogsFilterValue::STATUS_*
 * - events: INCLUDE_EVENT, NOT_INCLUDE_EVENT — value: EmailLogsFilterValue::EVENT_*
 * - clicks_count, opens_count: EQUAL, GREATER_THAN, LESS_THAN — value: int
 * - client_ip, sending_ip: EQUAL, NOT_EQUAL, CONTAIN, NOT_CONTAIN
 * - email_service_provider, category: EQUAL, NOT_EQUAL — value: string or array
 * - sending_domain_id: EQUAL, NOT_EQUAL — value: int or int[]
 * - sending_stream: EQUAL, NOT_EQUAL — value: EmailLogsFilterValue::STREAM_*
 */
final class EmailLogsFilterOperator
{
    /** Case-insensitive equal / not equal / contain / not contain (to, from, subject, email_service_provider_response, recipient_mx) */
    public const CI_EQUAL = 'ci_equal';
    public const CI_NOT_EQUAL = 'ci_not_equal';
    public const CI_CONTAIN = 'ci_contain';
    public const CI_NOT_CONTAIN = 'ci_not_contain';

    /** Subject only: has no value — use FilterCriterion::withoutValue() */
    public const EMPTY = 'empty';
    public const NOT_EMPTY = 'not_empty';

    /** Equal / not equal (status, category, email_service_provider, sending_domain_id, sending_stream) */
    public const EQUAL = 'equal';
    public const NOT_EQUAL = 'not_equal';

    /** Events only */
    public const INCLUDE_EVENT = 'include_event';
    public const NOT_INCLUDE_EVENT = 'not_include_event';

    /** Numeric (clicks_count, opens_count) */
    public const GREATER_THAN = 'greater_than';
    public const LESS_THAN = 'less_than';

    /** client_ip, sending_ip */
    public const CONTAIN = 'contain';
    public const NOT_CONTAIN = 'not_contain';

    private function __construct()
    {
    }
}
