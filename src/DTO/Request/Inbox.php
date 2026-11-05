<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request;

use Mailtrap\Helper\Deprecation;

/**
 * @deprecated Use {@see Sandbox} instead.
 *
 * Class Inbox
 */
class Inbox extends Sandbox
{
    public function __construct(?string $name = null, ?string $emailUsername = null)
    {
        Deprecation::trigger(self::class, Sandbox::class);

        parent::__construct($name, $emailUsername);
    }
}
