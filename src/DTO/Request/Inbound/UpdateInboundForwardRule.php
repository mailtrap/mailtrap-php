<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Inbound;

use Mailtrap\DTO\Request\RequestInterface;
use Mailtrap\Exception\InvalidArgumentException;

/**
 * Class UpdateInboundForwardRule
 */
final class UpdateInboundForwardRule implements RequestInterface
{
    /**
     * @param InboundForwardRuleCondition[]|null   $conditions
     * @param InboundForwardRuleDestination[]|null $destinations
     */
    public function __construct(
        private ?string $name = null,
        private ?array $conditions = null,
        private ?array $destinations = null,
    ) {
    }

    public function toArray(): array
    {
        $payload = [];

        if ($this->name !== null) {
            $payload['name'] = $this->name;
        }

        if ($this->conditions !== null) {
            $payload['conditions'] = array_values(array_map(
                static fn (InboundForwardRuleCondition $condition): array => $condition->toArray(),
                $this->conditions
            ));
        }

        if ($this->destinations !== null) {
            $payload['destinations'] = array_values(array_map(
                static fn (InboundForwardRuleDestination $destination): array => $destination->toArray(),
                $this->destinations
            ));
        }

        if ($payload === []) {
            throw new InvalidArgumentException('At least one updatable field must be provided to update a forward rule');
        }

        return $payload;
    }
}
