<?php

use Mailtrap\Config;
use Mailtrap\DTO\Request\Contact\CreateContact;
use Mailtrap\DTO\Request\Contact\CreateContactEvent;
use Mailtrap\DTO\Request\Contact\ImportContact;
use Mailtrap\DTO\Request\Contact\UpdateContact;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapGeneralClient;
use Mailtrap\DTO\Request\Contact\ContactExportFilter;

require __DIR__ . '/../vendor/autoload.php';

$accountId = $_ENV['MAILTRAP_ACCOUNT_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens
$contacts = (new MailtrapGeneralClient($config))->contacts($accountId);

/**
 * Get all Contact Lists.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/contacts/lists
 */
try {
    $response = $contacts->getAllContactLists();

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}


/**
 * Get a specific Contact List by ID.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/contacts/lists/{list_id}
 */
try {
    $contactListId = 1; // Replace 1 with the actual list ID
    $response = $contacts->getContactList($contactListId);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}


/**
 * Create a new Contact List.
 *
 * POST https://mailtrap.io/api/accounts/{account_id}/contacts/lists
 */
try {
    $contactListName = 'New Contact List'; // Replace with your desired list name
    $response = $contacts->createContactList($contactListName);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}


/**
 * Update a Contact List by ID.
 *
 * PATCH https://mailtrap.io/api/accounts/{account_id}/contacts/lists/{list_id}
 */
try {
    $contactListId = 1; // Replace 1 with the actual list ID
    $newContactListName = 'Updated Contact List Name'; // Replace with your desired list name
    $response = $contacts->updateContactList($contactListId, $newContactListName);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}


/**
 * Delete a Contact List by ID.
 *
 * DELETE https://mailtrap.io/api/accounts/{account_id}/contacts/lists/{list_id}
 */
try {
    $contactListId = 1; // Replace 1 with the actual list ID
    $response = $contacts->deleteContactList($contactListId);

    // Print the response status code
    var_dump($response->getStatusCode());
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}
