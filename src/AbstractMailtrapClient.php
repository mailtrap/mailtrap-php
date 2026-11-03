<?php

declare(strict_types=1);

namespace Mailtrap;

use Mailtrap\Exception\BadMethodCallException;

/**
 * Class AbstractMailtrapClient
 */
abstract class AbstractMailtrapClient implements MailtrapClientInterface
{
    public function __construct(protected ConfigInterface $config)
    {
    }

    public function __call(string $name, array $arguments)
    {
        $className = $this->getClassByName($name);
        if (null === $className) {
            throw new BadMethodCallException(sprintf('%s -> undefined method called: "%s"', static::class, $name));
        }

        /** @psalm-suppress LessSpecificReturnStatement */
        return new $className($this->getConfig(), ...$arguments);
    }

    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    private function getClassByName(string $name): ?string
    {
        /** @psalm-suppress UndefinedConstant */
        return !empty(static::API_MAPPING[$name]) ? static::API_MAPPING[$name] : null;
    }
}
