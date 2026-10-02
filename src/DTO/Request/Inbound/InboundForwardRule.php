<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Inbound;

/**
 * Forward rule vocabulary: match types and operators.
 */
final class InboundForwardRule
{
    public const MATCH_TYPE_SENDER = 'sender';
    public const MATCH_TYPE_RECIPIENT = 'recipient';
    public const MATCH_TYPE_HEADER = 'header';

    public const OPERATOR_EQUAL = 'equal';
    public const OPERATOR_NOT_EQUAL = 'not_equal';
    public const OPERATOR_CONTAINS = 'contains';
    public const OPERATOR_STARTS_WITH = 'starts_with';
    public const OPERATOR_ENDS_WITH = 'ends_with';
    public const OPERATOR_EMPTY = 'empty';
    public const OPERATOR_NOT_EMPTY = 'not_empty';

    public const MATCH_TYPES = [
        self::MATCH_TYPE_SENDER,
        self::MATCH_TYPE_RECIPIENT,
        self::MATCH_TYPE_HEADER,
    ];

    public const OPERATORS = [
        self::OPERATOR_EQUAL,
        self::OPERATOR_NOT_EQUAL,
        self::OPERATOR_CONTAINS,
        self::OPERATOR_STARTS_WITH,
        self::OPERATOR_ENDS_WITH,
        self::OPERATOR_EMPTY,
        self::OPERATOR_NOT_EMPTY,
    ];

    private function __construct()
    {
    }
}
