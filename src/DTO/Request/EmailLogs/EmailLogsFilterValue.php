<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\EmailLogs;

/**
 * All supported value constants for Email Logs list filters where the API accepts a fixed set.
 * Use with FilterCriterion::withValue() and the appropriate operator (e.g. EmailLogsFilterOperator::EQUAL).
 */
final class EmailLogsFilterValue
{
    /** status filter */
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_NOT_DELIVERED = 'not_delivered';
    public const STATUS_ENQUEUED = 'enqueued';
    public const STATUS_OPTED_OUT = 'opted_out';

    /** events filter */
    public const EVENT_DELIVERY = 'delivery';
    public const EVENT_OPEN = 'open';
    public const EVENT_CLICK = 'click';
    public const EVENT_BOUNCE = 'bounce';
    public const EVENT_SPAM = 'spam';
    public const EVENT_UNSUBSCRIBE = 'unsubscribe';
    public const EVENT_SOFT_BOUNCE = 'soft_bounce';
    public const EVENT_REJECT = 'reject';
    public const EVENT_SUSPENSION = 'suspension';

    /** sending_stream filter */
    public const STREAM_TRANSACTIONAL = 'transactional';
    public const STREAM_BULK = 'bulk';

    private function __construct()
    {
    }
}
