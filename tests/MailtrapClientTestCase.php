<?php

declare(strict_types=1);

namespace Mailtrap\Tests;

use Mailtrap\ConfigInterface;
use Mailtrap\Exception\BadMethodCallException;
use Mailtrap\MailtrapClientInterface;

/**
 * Class MailtrapClientTestCase
 */
abstract class MailtrapClientTestCase extends MailtrapTestCase
{
    protected ?MailtrapClientInterface $mailtrapClientLayer;

    abstract public function getMailtrapClientClassName(): string;

    abstract public function getLayerInterfaceClassName(): string;

    abstract public function mapInstancesProvider(): iterable;

    protected function setUp(): void
    {
        parent::setUp();

        $className = $this->getMailtrapClientClassName();
        $this->mailtrapClientLayer = new $className($this->createMock(ConfigInterface::class));
    }

    protected function tearDown(): void
    {
        $this->mailtrapClientLayer = null;

        parent::tearDown();
    }

    /**
     * @dataProvider invalidApiClassNameProvider
     */
    public function testInvalidApiClassName($name): void
    {
        $this->expectExceptionObject(
            new BadMethodCallException(
                sprintf('%s -> undefined method called: "%s"', $this->getMailtrapClientClassName(), $name)
            )
        );

        $this->mailtrapClientLayer->{$name}();
    }

    /**
     * @dataProvider mapInstancesProvider
     */
    public function testMapInstance($instance): void
    {
        $this->assertInstanceOf($this->getLayerInterfaceClassName(), $instance);
    }

    public function invalidApiClassNameProvider(): array
    {
        return [['fakeclass1'], ['fakeclass2']];
    }
}
