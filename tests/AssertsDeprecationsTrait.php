<?php

declare(strict_types=1);

namespace Mailtrap\Tests;

use Mailtrap\Helper\Deprecation;

/**
 * Captures the SDK's silenced E_USER_DEPRECATED notices, which PHPUnit's own error handler ignores.
 */
trait AssertsDeprecationsTrait
{
    /**
     * Runs $call, asserts it triggered a deprecation notice containing $needle and returns what $call returned.
     */
    protected function assertDeprecationTriggered(string $needle, callable $call): mixed
    {
        [$result, $messages] = $this->captureDeprecations($call);

        self::assertNotEmpty(
            array_filter($messages, static fn (string $message): bool => str_contains($message, $needle)),
            sprintf('No deprecation notice containing "%s" was triggered. Triggered: %s', $needle, json_encode($messages))
        );

        return $result;
    }

    /**
     * Runs $call, asserts it triggered no deprecation notice and returns what $call returned.
     */
    protected function assertNoDeprecationTriggered(callable $call): mixed
    {
        [$result, $messages] = $this->captureDeprecations($call);

        self::assertSame([], $messages, 'Unexpected deprecation notices were triggered.');

        return $result;
    }

    /**
     * Runs $call and returns what it returned and the deprecation notices it triggered.
     *
     * @return array{0: mixed, 1: string[]}
     */
    protected function captureDeprecations(callable $call): array
    {
        Deprecation::reset();
        $messages = [];
        set_error_handler(static function (int $level, string $message) use (&$messages): bool {
            $messages[] = $message;

            return true;
        }, E_USER_DEPRECATED);

        try {
            $result = $call();
        } finally {
            restore_error_handler();
        }

        return [$result, $messages];
    }
}
