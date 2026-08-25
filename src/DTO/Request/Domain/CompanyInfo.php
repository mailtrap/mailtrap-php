<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Domain;

/**
 * Company info vocabulary: sender info levels.
 */
final class CompanyInfo
{
    public const INFO_LEVEL_BUSINESS = 'business';
    public const INFO_LEVEL_INDIVIDUAL = 'individual';

    private function __construct()
    {
    }
}
