<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Webhook;

/**
 * Webhook vocabulary: types, events, payload formats and sending streams.
 */
final class Webhook
{
    public const TYPE_EMAIL_SENDING = 'email_sending';
    public const TYPE_AUDIT_LOG = 'audit_log';

    public const PAYLOAD_FORMAT_JSON = 'json';
    public const PAYLOAD_FORMAT_JSONLINES = 'jsonlines';

    public const SENDING_STREAM_TRANSACTIONAL = 'transactional';
    public const SENDING_STREAM_BULK = 'bulk';

    public const EVENT_DELIVERY = 'delivery';
    public const EVENT_BOUNCE = 'bounce';
    public const EVENT_SOFT_BOUNCE = 'soft_bounce';
    public const EVENT_SUSPENSION = 'suspension';
    public const EVENT_UNSUBSCRIBE = 'unsubscribe';
    public const EVENT_OPEN = 'open';
    public const EVENT_SPAM_COMPLAINT = 'spam_complaint';
    public const EVENT_CLICK = 'click';
    public const EVENT_REJECT = 'reject';

    private function __construct()
    {
    }
}
