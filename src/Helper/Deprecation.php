<?php

declare(strict_types=1);

namespace Mailtrap\Helper;

use Mailtrap\Exception\InvalidArgumentException;

/**
 * Triggers silenced E_USER_DEPRECATED notices for renamed SDK names, once per process for each old name.
 * Framework error handlers (Symfony, Laravel) log silenced deprecations; plain PHP ignores them.
 *
 * @internal
 */
final class Deprecation
{
    /**
     * @var array<string, true>
     */
    private static array $triggered = [];

    public static function trigger(string $old, string $new): void
    {
        if (isset(self::$triggered[$old])) {
            return;
        }
        self::$triggered[$old] = true;

        @trigger_error(
            sprintf('%s is deprecated and will be removed in a future major release; use %s instead.', $old, $new),
            E_USER_DEPRECATED
        );
    }

    /**
     * Resolves an argument renamed from $oldName to $newName, which callers may still pass by its old name.
     * Passing both names with different values is an error.
     */
    public static function renamedArgument(
        string $function,
        string $oldName,
        string $newName,
        mixed $oldValue,
        mixed $newValue,
    ): mixed {
        if (null === $oldValue) {
            return $newValue;
        }

        self::trigger(sprintf('The $%s argument of %s', $oldName, $function), sprintf('$%s', $newName));

        if (null !== $newValue && $newValue !== $oldValue) {
            throw new InvalidArgumentException(
                sprintf('%s received different values for $%s and $%s; pass only $%s.', $function, $newName, $oldName, $newName)
            );
        }

        return $oldValue;
    }

    /**
     * Forgets which notices were triggered, so tests can assert each one.
     */
    public static function reset(): void
    {
        self::$triggered = [];
    }
}
