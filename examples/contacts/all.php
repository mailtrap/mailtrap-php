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
 * Get contact
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/contacts/{id_or_email}
 */
try {
    // Get contact by ID
    $response = $contacts->getContactById('019706a8-0000-0000-0000-4f26816b467a');

    // OR get contact by email
    $response = $contacts->getContactByEmail('john.smith@example.com');

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}


/**
 * Create a new Contact
 *
 * POST https://mailtrap.io/api/accounts/{account_id}/contacts
 */
try {
    $response = $contacts->createContact(
        CreateContact::init(
            'john.smith@example.com',
            ['first_name' => 'John', 'last_name' => 'Smith'], // Fields
            [1, 2] // List IDs to which the contact will be added
        )
    );

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}


/**
 * Update contact by ID or Email.
 *
 * PATCH https://mailtrap.io/api/accounts/{account_id}/contacts/{id_or_email}
 */
try {
    // Update contact by ID
    $response = $contacts->updateContactById(
        '019706a8-0000-0000-0000-4f26816b467a',
        UpdateContact::init(
            'john.smith@example.com',
            ['first_name' => 'John', 'last_name' => 'Smith'], // Fields
            [3], // List IDs to which the contact will be added
            [1, 2], // List IDs from which the contact will be removed
            true // Unsubscribe the contact
        )
    );

    // OR update contact by email
    $response = $contacts->updateContactByEmail(
        'john.smith@example.com',
        UpdateContact::init(
            'john.smith@example.com',
            ['first_name' => 'John', 'last_name' => 'Smith'], // Fields
            [3], // List IDs to which the contact will be added
            [1, 2], // List IDs from which the contact will be removed
            true // Unsubscribe the contact
        )
    );

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}


/**
 * Delete contact
 *
 * Delete https://mailtrap.io/api/accounts/{account_id}/contacts/{id_or_email}
 */
try {
    // Delete contact by ID
    $response = $contacts->deleteContactById('019706a8-0000-0000-0000-4f26816b467a');

    // OR delete contact by email
    $response = $contacts->deleteContactByEmail('john.smith@example.com');

    // Print the response status code
    var_dump($response->getStatusCode());
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}


/**
 * Import contacts in bulk with support for custom fields and list management.
 * Existing contacts with matching email addresses will be updated automatically.
 * You can import up to 50,000 contacts per request.
 * The import process runs asynchronously - use the returned import ID to check the status and results.
 *
 * POST https://mailtrap.io/api/accounts/{account_id}/contacts/imports
 */
try {
    $contactsToImport = [
        new ImportContact(
            email: 'customer1@example.com',
            fields: ['first_name' => 'John', 'last_name' => 'Smith', 'zip_code' => 11111],
            listIdsIncluded: [1, 2],
            listIdsExcluded: [4, 5]
        ),
        new ImportContact(
            email: 'customer2@example.com',
            fields: ['first_name' => 'Joe', 'last_name' => 'Doe', 'zip_code' => 22222],
            listIdsIncluded: [1],
            listIdsExcluded: [4]
        ),
    ];

    $response = $contacts->importContacts($contactsToImport);
    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}


/**
 * Get the status of a contact import by ID.
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/contacts/imports/{import_id}
 */
try {
    $importId = 1; // Replace 1 with the actual import ID
    $response = $contacts->getContactImport($importId);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}


/**
 * Create a new Contact Event
 *
 * POST https://mailtrap.io/api/accounts/{account_id}/contacts/{contact_identifier}/events
 */
try {
    // Create event using contact email
    $response = $contacts->createContactEvent(
        'john.smith@example.com', // Contact identifier (email or UUID)
        CreateContactEvent::init(
            'UserLogin',
            [
                'user_id' => 101,
                'user_name' => 'John Smith',
                'is_active' => true,
                'last_seen' => null
            ]
        )
    );

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}

/**
 * Create a new Contact Export (asynchronous task)
 *
 * POST https://mailtrap.io/api/accounts/{account_id}/contacts/exports
 */
try {
    $filters = [
        // Export contacts that belong to lists 1 or 2
        ContactExportFilter::init('list_id', 'equal', [1, 2]),
        // Only subscribed contacts
        ContactExportFilter::init('subscription_status', 'equal', 'subscribed'),
    ];

    $response = $contacts->createContactExport($filters);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}


/**
 * Get Contact Export status / download URL
 * (Poll this endpoint until status becomes `finished` and `url` is not null)
 *
 * GET https://mailtrap.io/api/accounts/{account_id}/contacts/exports/{export_id}
 */
try {
    $exportId = 1; // Replace 1 with the actual export ID obtained from createContactExport
    $response = $contacts->getContactExport($exportId);

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
}
