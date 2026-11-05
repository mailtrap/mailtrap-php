<?php

declare(strict_types=1);

namespace Mailtrap\Api\Sandbox;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Project
 */
class Project extends AbstractApi implements SandboxInterface
{
    public function __construct(ConfigInterface $config, private int $accountId)
    {
        parent::__construct($config);
    }

    /**
     * List projects and their sandboxes to which the API token has access.
     *
     * @return ResponseInterface
     */
    public function getList(): ResponseInterface
    {
        return $this->handleResponse($this->httpGet(
            sprintf('%s/api/accounts/%s/projects', $this->getHost(), $this->getAccountId())
        ));
    }

    /**
     * Get the project and its sandboxes.
     *
     * @param int $projectId
     *
     * @return ResponseInterface
     */
    public function getById(int $projectId): ResponseInterface
    {
        return $this->handleResponse($this->httpGet(
            sprintf('%s/api/accounts/%s/projects/%s', $this->getHost(), $this->getAccountId(), $projectId)
        ));
    }

    /**
     * Create a project
     *
     * @param string $projectName
     *
     * @return ResponseInterface
     */
    public function create(string $projectName): ResponseInterface
    {
        return $this->handleResponse($this->httpPost(
            sprintf('%s/api/accounts/%s/projects', $this->getHost(), $this->getAccountId()),
            [],
            ['project' => ['name' => $projectName]]
        ));
    }

    /**
     * Delete project and its sandboxes.
     *
     * @param int $projectId
     *
     * @return ResponseInterface
     */
    public function delete(int $projectId): ResponseInterface
    {
        return $this->handleResponse($this->httpDelete(
            sprintf('%s/api/accounts/%s/projects/%s', $this->getHost(), $this->getAccountId(), $projectId)
        ));
    }

    /**
     * Update project name.
     *
     * @param int    $projectId
     * @param string $projectName
     *
     * @return ResponseInterface
     */
    public function updateName(int $projectId, string $projectName): ResponseInterface
    {
        return $this->handleResponse($this->httpPatch(
            sprintf('%s/api/accounts/%s/projects/%s', $this->getHost(), $this->getAccountId(), $projectId),
            [],
            ['project' => ['name' => $projectName]]
        ));
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }
}
