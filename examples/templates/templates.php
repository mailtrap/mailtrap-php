<?php

declare(strict_types=1);

use Mailtrap\Config;
use Mailtrap\DTO\Request\Template\CreateTemplate;
use Mailtrap\DTO\Request\Template\UpdateTemplate;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapGeneralClient;

require __DIR__ . '/../../vendor/autoload.php';

$accountId = (int) $_ENV['MAILTRAP_ACCOUNT_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']); // your API token from https://mailtrap.io/api-tokens
$templates = (new MailtrapGeneralClient($config))->templates($accountId);

/**
 * Get a paginated list of templates.
 * Optional filters: $perPage (max 100, default 50), $token (page number).
 *
 * The response is wrapped in `{ data: [...], pagination }`.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/templates
 */
try {
    $response = $templates->getTemplates(perPage: 50, token: 1);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}

/**
 * Create a new template (wrapped in `data`).
 * `name`, `subject` and `category` are required.
 *
 * POST https://mailtrap.io/api/accounts/{account_id}/templates
 */
try {
    $response = $templates->createTemplate(new CreateTemplate(
        name: 'Welcome Email',
        subject: 'Welcome to our service!',
        category: 'Transactional',
        bodyHtml: '<div>Welcome to our service!</div>',
        bodyText: 'Welcome to our service!',
    ));

    $template = ResponseHelper::toArray($response);
    $templateId = $template['data']['id']; // reused by all the examples below

    var_dump($template);
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
    exit(1);
}

/**
 * Get a template by ID (wrapped in `data`).
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/templates/{template_id}
 */
try {
    $response = $templates->getTemplate($templateId);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}

/**
 * Update a template (PATCH, partial: only the provided fields are sent).
 *
 * PATCH https://mailtrap.io/api/accounts/{account_id}/templates/{template_id}
 */
try {
    $response = $templates->updateTemplate(
        $templateId,
        new UpdateTemplate(subject: 'Updated subject', bodyHtml: '<div>Updated HTML body</div>')
    );

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}

/**
 * Delete a template. Returns `204 No Content`.
 *
 * DELETE https://mailtrap.io/api/accounts/{account_id}/templates/{template_id}
 */
try {
    $response = $templates->deleteTemplate($templateId);

    var_dump($response->getStatusCode());
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}
