<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Inbound;

use Mailtrap\DTO\Request\RequestInterface;
use Mailtrap\Exception\InvalidArgumentException;

/**
 * Class InboundForwardRuleCondition
 */
final class InboundForwardRuleCondition implements RequestInterface
{
    public function __construct(
        private string $matchType,
        private string $operator,
        private ?string $value = null,
        private ?string $headerKey = null,
    ) {
        if (!in_array($matchType, InboundForwardRule::MATCH_TYPES, true)) {
            throw new InvalidArgumentException(sprintf(
                '"matchType" must be one of "%s", "%s" given',
                implode('", "', InboundForwardRule::MATCH_TYPES),
                $matchType
            ));
        }

        if (!in_array($operator, InboundForwardRule::OPERATORS, true)) {
            throw new InvalidArgumentException(sprintf(
                '"operator" must be one of "%s", "%s" given',
                implode('", "', InboundForwardRule::OPERATORS),
                $operator
            ));
        }
    }

    public function toArray(): array
    {
        $payload = [
            'match_type' => $this->matchType,
            'operator' => $this->operator,
        ];

        if ($this->value !== null) {
            $payload['value'] = $this->value;
        }

        if ($this->headerKey !== null) {
            $payload['header_key'] = $this->headerKey;
        }

        return $payload;
    }
}
