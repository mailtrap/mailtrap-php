<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request;

/**
 * Class Sandbox
 */
class Sandbox implements RequestInterface
{
    public function __construct(private ?string $name = null, private ?string $emailUsername = null)
    {
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getEmailUsername(): ?string
    {
        return $this->emailUsername;
    }

    public function toArray(): array
    {
        $array = [];

        if (!empty($this->getName())) {
            $array['name'] = $this->getName();
        }

        if (!empty($this->getEmailUsername())) {
            $array['email_username'] = $this->getEmailUsername();
        }

        return $array;
    }
}
