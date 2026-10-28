<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\MarketingStats;

use Mailtrap\DTO\Request\RequestInterface;

interface MarketingStatsInterface extends RequestInterface
{
    // Email Marketing traffic sources (the `sources[]` filter)
    public const SOURCE_CAMPAIGNS = 'campaigns';
    public const SOURCE_AUTOMATIONS = 'automations';

    public const SOURCES = [
        self::SOURCE_CAMPAIGNS,
        self::SOURCE_AUTOMATIONS,
    ];
}
