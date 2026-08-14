<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\EmailCampaign;

use Mailtrap\DTO\Request\RequestInterface;

interface EmailCampaignInterface extends RequestInterface
{
    // Delivery modes (EmailCampaign.delivery_mode)
    public const DELIVERY_MODE_RAPID = 'rapid';
    public const DELIVERY_MODE_GRADUAL = 'gradual';

    // Lifecycle states (EmailCampaign.current_state)
    public const STATE_DRAFT = 'draft';
    public const STATE_SCHEDULED = 'scheduled';
    public const STATE_STARTED = 'started';
    public const STATE_QUEUED = 'queued';
    public const STATE_PAUSED = 'paused';
    public const STATE_TERMINATING = 'terminating';
    public const STATE_UNDER_REVIEW = 'under_review';
    public const STATE_FINISHED = 'finished';
    public const STATE_FAILED = 'failed';
    public const STATE_FAILED_IMMEDIATELY = 'failed_immediately';
}
