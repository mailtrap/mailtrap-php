<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Helper;

use Mailtrap\Exception\InvalidArgumentException;
use Mailtrap\Helper\Deprecation;
use Mailtrap\Tests\AssertsDeprecationsTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mailtrap\Helper\Deprecation
 *
 * Class DeprecationTest
 */
class DeprecationTest extends TestCase
{
    use AssertsDeprecationsTrait;

    public function testTriggersOncePerOldName(): void
    {
        [, $messages] = $this->captureDeprecations(static function (): void {
            Deprecation::trigger('Foo::old()', 'Foo::new()');
            Deprecation::trigger('Foo::old()', 'Foo::new()');
            Deprecation::trigger('Bar::old()', 'Bar::new()');
        });

        $this->assertSame([
            'Foo::old() is deprecated and will be removed in a future major release; use Foo::new() instead.',
            'Bar::old() is deprecated and will be removed in a future major release; use Bar::new() instead.',
        ], $messages);
    }

    public function testRenamedArgumentReturnsNewValueSilently(): void
    {
        $this->assertNoDeprecationTriggered(function (): void {
            $this->assertSame(1, Deprecation::renamedArgument('foo()', 'old', 'new', null, 1));
            $this->assertNull(Deprecation::renamedArgument('foo()', 'old', 'new', null, null));
        });
    }

    public function testRenamedArgumentFallsBackToOldValue(): void
    {
        [, $messages] = $this->captureDeprecations(function (): void {
            $this->assertSame(1, Deprecation::renamedArgument('foo()', 'old', 'new', 1, null));
            $this->assertSame(1, Deprecation::renamedArgument('foo()', 'old', 'new', 1, 1));
        });

        $this->assertSame(
            ['The $old argument of foo() is deprecated and will be removed in a future major release; use $new instead.'],
            $messages
        );
    }

    public function testRenamedArgumentRejectsDifferentValues(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('foo() received different values for $new and $old; pass only $new.')
        );

        $this->captureDeprecations(static fn () => Deprecation::renamedArgument('foo()', 'old', 'new', 1, 2));
    }
}
