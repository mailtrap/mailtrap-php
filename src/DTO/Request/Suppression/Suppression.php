<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Suppression;

/**
 * Suppression vocabulary: sending streams and suppression types.
 */
final class Suppression
{
    public const SENDING_STREAM_TRANSACTIONAL = 'transactional';
    public const SENDING_STREAM_BULK = 'bulk';

    public const TYPE_HARD_BOUNCE = 'hard bounce';
    public const TYPE_UNSUBSCRIPTION = 'unsubscription';
    public const TYPE_SPAM_COMPLAINT = 'spam complaint';
    public const TYPE_MANUAL_IMPORT = 'manual import';
    /**
     * A contact's unsubscription from a subscription topic. Returned only by the suppressions list,
     * with SuppressionsFilter::$includeTopicUnsubscriptions; it can't be created or deleted here.
     */
    public const TYPE_TOPIC_UNSUBSCRIPTION = 'topic unsubscription';

    private function __construct()
    {
    }
}
