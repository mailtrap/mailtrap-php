<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Inbound;

use Mailtrap\DTO\Request\RequestInterface;

/**
 * Class CreateInboundForwardRule
 */
final class CreateInboundForwardRule implements RequestInterface
{
    /**
     * @param InboundForwardRuleCondition[]|null   $conditions
     * @param InboundForwardRuleDestination[]|null $destinations
     */
    public function __construct(
        private string $name,
        private ?array $conditions = null,
        private ?array $destinations = null,
    ) {
    }

    public function toArray(): array
    {
        $payload = [
            'name' => $this->name,
        ];

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

        return $payload;
    }
}
