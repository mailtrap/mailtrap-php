<?php

declare(strict_types=1);

namespace Mailtrap\Api\General;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Mailtrap\DTO\Request\EmailTemplate as EmailTemplateDTO;
use Psr\Http\Message\ResponseInterface;

/**
 * Class EmailTemplate
 *
 * For the paginated, experimental templates endpoints, see {@see Template}.
 */
class EmailTemplate extends AbstractApi implements GeneralInterface
{
    public function __construct(ConfigInterface $config, private int $accountId)
    {
        parent::__construct($config);
    }

    /**
     * Get all Email Templates.
     *
     * @return ResponseInterface
     */
    public function getAllEmailTemplates(): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath())
        );
    }

    /**
     * Get an Email Template by ID.
     *
     * @param int $templateId
     * @return ResponseInterface
     */
    public function getEmailTemplate(int $templateId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath() . '/' . $templateId)
        );
    }

    /**
     * Create a new Email Template.
     *
     * @param EmailTemplateDTO $emailTemplate
     * @return ResponseInterface
     */
    public function createEmailTemplate(EmailTemplateDTO $emailTemplate): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost(
                path: $this->getBasePath(),
                body: ['email_template' => $emailTemplate->toArray()]
            )
        );
    }

    /**
     * Update an existing Email Template by ID.
     *
     * @param int $templateId
     * @param EmailTemplateDTO $template
     * @return ResponseInterface
     */
    public function updateEmailTemplate(int $templateId, EmailTemplateDTO $template): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPatch(
                path: $this->getBasePath() . '/' . $templateId,
                body: ['email_template' => $template->toArray()]
            )
        );
    }

    /**
     * Delete an Email Template by ID.
     *
     * @param int $templateId
     * @return ResponseInterface
     */
    public function deleteEmailTemplate(int $templateId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpDelete($this->getBasePath() . '/' . $templateId)
        );
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/accounts/%s/email_templates', $this->getHost(), $this->accountId);
    }
}
