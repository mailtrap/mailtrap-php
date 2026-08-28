<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\ApiToken;

use DateTimeInterface;

/**
 * Optional token expiration as an ISO 8601 date-time.
 * Omit the argument for the server default (a 1-year default is being rolled out).
 * Use TokenExpiration::never() for a token that never expires.
 * Past or more-than-5-years-ahead values are rejected with 422.
 *
 * Class TokenExpiration
 */
final class TokenExpiration
{
    private function __construct(private ?string $value)
    {
    }

    /**
     * Expire the token at the given ISO 8601 date-time.
     *
     * @param DateTimeInterface|string $value
     *
     * @return self
     */
    public static function at(DateTimeInterface|string $value): self
    {
        return new self(
            $value instanceof DateTimeInterface ? $value->format(DateTimeInterface::ATOM) : $value
        );
    }

    /**
     * Token never expires.
     *
     * @return self
     */
    public static function never(): self
    {
        return new self(null);
    }

    public function getValue(): ?string
    {
        return $this->value;
    }
}
