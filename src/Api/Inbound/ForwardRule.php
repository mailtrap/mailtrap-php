<?php

declare(strict_types=1);

namespace Mailtrap\Api\Inbound;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Mailtrap\DTO\Request\Inbound\CreateInboundForwardRule;
use Mailtrap\DTO\Request\Inbound\UpdateInboundForwardRule;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ForwardRule
 *
 * Forward rules of an inbound inbox.
 */
class ForwardRule extends AbstractApi implements InboundInterface
{
    public function __construct(ConfigInterface $config, private int $inboxId)
    {
        parent::__construct($config);
    }

    public function getList(): ResponseInterface
    {
        return $this->handleResponse($this->httpGet($this->getBasePath()));
    }

    public function getById(int $ruleId): ResponseInterface
    {
        return $this->handleResponse($this->httpGet($this->getBasePath() . '/' . $ruleId));
    }

    public function create(CreateInboundForwardRule $rule): ResponseInterface
    {
        return $this->handleResponse($this->httpPost($this->getBasePath(), [], $rule->toArray()));
    }

    public function update(int $ruleId, UpdateInboundForwardRule $rule): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPatch($this->getBasePath() . '/' . $ruleId, [], $rule->toArray())
        );
    }

    public function delete(int $ruleId): ResponseInterface
    {
        return $this->handleResponse($this->httpDelete($this->getBasePath() . '/' . $ruleId));
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/inbound/inboxes/%s/forward_rules', $this->getHost(), $this->inboxId);
    }
}
