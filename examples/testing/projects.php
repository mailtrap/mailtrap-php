<?php

use Mailtrap\Config;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapSandboxClient;

require __DIR__ . '/../vendor/autoload.php';

$accountId = $_ENV['MAILTRAP_ACCOUNT_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens

$sandboxProjects = (new MailtrapSandboxClient($config))->projects($accountId); #required parameter is accountId

/**
 * List projects and their inboxes to which the API token has access.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/projects
 */
try {
    $response = $sandboxProjects->getList();

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Get the project and its inboxes.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/projects/{project_id}
 */
try {
    $projectId = $_ENV['MAILTRAP_PROJECT_ID'];

    $response = $sandboxProjects->getById($projectId);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

/**
 * Create project
 * The project name is min 2 characters and max 100 characters long.
 *
 * POST https://mailtrap.io/api/accounts/{account_id}/projects
 */
try {
    $projectName = 'Some project name';

    $response = $sandboxProjects->create($projectName);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Update project
 * The project name is min 2 characters and max 100 characters long.
 *
 * PATCH https://mailtrap.io/api/accounts/{account_id}/projects/{project_id}
 */
try {
    $projectId = $_ENV['MAILTRAP_PROJECT_ID'];
    $newProjectName = 'New project name';

    $response = $sandboxProjects->updateName($projectId, $newProjectName);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Delete project and its inboxes.
 *
 * DELETE https://mailtrap.io/api/accounts/{account_id}/projects/{project_id}
 */
try {
    $projectId = $_ENV['MAILTRAP_PROJECT_ID'];

    $response = $sandboxProjects->delete($projectId);

    // Print the response status code
    var_dump($response->getStatusCode());
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


