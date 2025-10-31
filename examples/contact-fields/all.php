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
 * Get all Contact Fields existing in your account
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/contacts/fields
 */
try {
    $response = $contacts->getAllContactFields();

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}


/**
 * Get a specific Contact Field by ID.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/contacts/fields/{field_id}
 */
try {
    $fieldId = 1; // Replace 1 with the actual field ID
    $response = $contacts->getContactField($fieldId);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}


/**
 * Create new Contact Fields. Please note, you can have up to 40 fields.
 *
 * POST https://mailtrap.io/api/accounts/{account_id}/contacts/fields
 */
try {
    $response = $contacts->createContactField(
        'New Field Name', // <= 80 characters
        'text', // Allowed values: text, integer, float, boolean, date
        'new_field_merge_tag' // Personalize your campaigns by adding a merge tag. This field will be replaced with unique contact details for each recipient.
    );

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}


/**
 * Update existing Contact Field. Please note, you cannot change data_type of the field.
 *
 * PATCH https://mailtrap.io/api/accounts/{account_id}/contacts/fields/{field_id}
 */
try {
    $fieldId = 1; // Replace 1 with the actual field ID
    $response = $contacts->updateContactField(
        $fieldId,
        'Updated Field Name',
        'updated_field_merge_tag'
    );

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}

/**
 * Delete Contact Field by ID.
 *
 * DELETE https://mailtrap.io/api/accounts/{account_id}/contacts/fields/{field_id}
 */
try {
    $fieldId = 1; // Replace 1 with the actual field ID
    $response = $contacts->deleteContactField($fieldId);

    // Print the response status code
    var_dump($response->getStatusCode());
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}
