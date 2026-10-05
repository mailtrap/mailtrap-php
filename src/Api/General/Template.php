<?php

declare(strict_types=1);

namespace Mailtrap\Api\General;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Mailtrap\DTO\Request\Template\CreateTemplate;
use Mailtrap\DTO\Request\Template\UpdateTemplate;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Template
 *
 * Templates API (`/api/templates`), the paginated replacement for {@see EmailTemplate}.
 */
class Template extends AbstractApi implements GeneralInterface
{
    public function __construct(ConfigInterface $config, private int $accountId)
    {
        parent::__construct($config);
    }

    /**
     * Get a paginated list of templates.
     * The response is wrapped in `{ data: [...], pagination }`.
     *
     * @param int|null $perPage Number of templates per page (max 100, default 50)
     * @param int|null $token   Page number to retrieve (page-token pagination, default 1)
     *
     * @return ResponseInterface
     */
    public function getTemplates(?int $perPage = null, ?int $token = null): ResponseInterface
    {
        $parameters = [];

        if ($perPage !== null) {
            $parameters['per_page'] = $perPage;
        }

        if ($token !== null) {
            $parameters['token'] = $token;
        }

        return $this->handleResponse(
            $this->httpGet($this->getBasePath(), $parameters)
        );
    }

    /**
     * Get a template by ID. The template is wrapped in `data`.
     *
     * @param int $templateId
     * @return ResponseInterface
     */
    public function getTemplate(int $templateId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath() . '/' . $templateId)
        );
    }

    /**
     * Create a new template. The template is wrapped in `data`.
     *
     * @param CreateTemplate $template
     * @return ResponseInterface
     */
    public function createTemplate(CreateTemplate $template): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost(
                path: $this->getBasePath(),
                body: $template->toArray()
            )
        );
    }

    /**
     * Update an existing template (PATCH, partial). The template is wrapped in `data`.
     *
     * @param int            $templateId
     * @param UpdateTemplate $template
     * @return ResponseInterface
     */
    public function updateTemplate(int $templateId, UpdateTemplate $template): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPatch(
                path: $this->getBasePath() . '/' . $templateId,
                body: $template->toArray()
            )
        );
    }

    /**
     * Delete a template. Returns `204 No Content` with an empty body.
     *
     * @param int $templateId
     * @return ResponseInterface
     */
    public function deleteTemplate(int $templateId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpDelete($this->getBasePath() . '/' . $templateId)
        );
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/accounts/%s/templates', $this->getHost(), $this->accountId);
    }
}
