<?php

declare(strict_types=1);

use Mailtrap\Config;
use Mailtrap\DTO\Request\EmailTemplate;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapGeneralClient;

require __DIR__ . '/../vendor/autoload.php';

$accountId = $_ENV['MAILTRAP_ACCOUNT_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']); // Your API token from https://mailtrap.io/api-tokens
$emailTemplates = (new MailtrapGeneralClient($config))->emailTemplates($accountId);

/**
 * Get all Email Templates.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/email_templates
 */
try {
    $response = $emailTemplates->getAllEmailTemplates();

    // Print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}


/**
 * Get Email Template by ID.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/email_templates/{id}
 */
try {
    $templateId = 12345; // Replace with a valid template ID
    $response = $emailTemplates->getEmailTemplate($templateId);

    // Print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}


/**
 * Create a new Email Template.
 *
 * POST https://mailtrap.io/api/accounts/{account_id}/email_templates
 */
try {
    $response = $emailTemplates->createEmailTemplate(
        EmailTemplate::init(
            'Welcome Email', // Name
            'Welcome to our service!', // Subject
            'Transactional', // Category
            'Welcome to our service!', // Text Body
            '<div>Welcome to our service!</div>' // HTML Body
        )
    );

    // Print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}


/**
 * Update an Email Template by ID.
 *
 * PATCH https://mailtrap.io/api/accounts/{account_id}/email_templates/{email_template_id}
 */
try {
    $templateId = 12345; // Replace with a valid template ID
    $response = $emailTemplates->updateEmailTemplate(
        $templateId,
        EmailTemplate::init(
            'Updated Welcome Email', // Name
            'Updated Subject', // Subject
            'Transactional', // Category
            'Updated Text Body', // Text Body
            '<div>Updated HTML Body</div>', // HTML Body
        )
    );

    // Print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}


/**
 * Delete an Email Template by ID.
 *
 * DELETE https://mailtrap.io/api/accounts/{account_id}/email_templates/{email_template_id}
 */
try {
    $templateId = 12345; // Replace with a valid template ID
    $response = $emailTemplates->deleteEmailTemplate($templateId);

    // Print the response status code
    var_dump($response->getStatusCode());
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), PHP_EOL;
}
