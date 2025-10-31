<?php

use Mailtrap\Config;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapGeneralClient;

require __DIR__ . '/../vendor/autoload.php';

$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens
$generalAccounts = (new MailtrapGeneralClient($config))->accounts();

/**
 * Get a list of your Mailtrap accounts.
 *
 * GET https://mailtrap.io/api/accounts
 */
try {
    $response = $generalAccounts->getList();

    var_dump(ResponseHelper::toArray($response)); // body (array)
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
