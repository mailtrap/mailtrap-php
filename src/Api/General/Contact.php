<?php

declare(strict_types=1);

namespace Mailtrap\Api\General;

use Mailtrap\Api\AbstractApi;
use Mailtrap\ConfigInterface;
use Mailtrap\DTO\Request\Contact\CreateContact;
use Mailtrap\DTO\Request\Contact\CreateContactEvent;
use Mailtrap\DTO\Request\Contact\ContactExportFilter;
use Mailtrap\DTO\Request\Contact\ImportContact;
use Mailtrap\DTO\Request\Contact\UpdateContact;
use Mailtrap\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Contact
 */
class Contact extends AbstractApi implements GeneralInterface
{
    public function __construct(ConfigInterface $config, private int $accountId)
    {
        parent::__construct($config);
    }

    /**
     * Get all Contact Lists.
     *
     * @return ResponseInterface
     */
    public function getAllContactLists(): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath() . '/lists')
        );
    }

    /**
     * Get a specific Contact List by ID.
     *
     * @param int $listId
     * @return ResponseInterface
     */
    public function getContactList(int $listId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath() . '/lists/' . $listId)
        );
    }

    /**
     * Create a new Contact List.
     *
     * @param string $name
     * @return ResponseInterface
     */
    public function createContactList(string $name): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost(
                path: $this->getBasePath() . '/lists',
                body: ['name' => $name]
            )
        );
    }

    /**
     * Update an existing Contact List by ID.
     *
     * @param int $listId
     * @param string $name
     * @return ResponseInterface
     */
    public function updateContactList(int $listId, string $name): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPatch(
                path: $this->getBasePath() . '/lists/' . $listId,
                body: ['name' => $name]
            )
        );
    }

    /**
     * Delete a Contact List by ID.
     *
     * @param int $listId
     * @return ResponseInterface
     */
    public function deleteContactList(int $listId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpDelete($this->getBasePath() . '/lists/' . $listId)
        );
    }

    /**
     * Get a Contact by ID (UUID)
     *
     * @param string $contactId
     * @return ResponseInterface
     */
    public function getContactById(string $contactId): ResponseInterface
    {
        return $this->getContact($contactId);
    }

    /**
     * Get a Contact by Email.
     *
     * @param string $email
     * @return ResponseInterface
     */
    public function getContactByEmail(string $email): ResponseInterface
    {
        return $this->getContact($email);
    }

    /**
     * Create a new Contact.
     *
     * @param CreateContact $contact
     * @return ResponseInterface
     */
    public function createContact(CreateContact $contact): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost(path: $this->getBasePath(), body: ['contact' => $contact->toArray()])
        );
    }

    /**
     * Update an existing Contact by ID (UUID).
     *
     * @param string $contactId
     * @param UpdateContact $contact
     * @return ResponseInterface
     */
    public function updateContactById(string $contactId, UpdateContact $contact): ResponseInterface
    {
        return $this->updateContact($contactId, $contact);
    }

    /**
     * Update an existing Contact by Email.
     *
     * @param string $email
     * @param UpdateContact $contact
     * @return ResponseInterface
     */
    public function updateContactByEmail(string $email, UpdateContact $contact): ResponseInterface
    {
        return $this->updateContact($email, $contact);
    }

    /**
     * Delete a Contact by ID (UUID).
     *
     * @param string $contactId
     * @return ResponseInterface
     */
    public function deleteContactById(string $contactId): ResponseInterface
    {
        return $this->deleteContact($contactId);
    }

    /**
     * Delete a Contact by Email.
     *
     * @param string $email
     * @return ResponseInterface
     */
    public function deleteContactByEmail(string $email): ResponseInterface
    {
        return $this->deleteContact($email);
    }

    /**
     * Get all Contact Fields.
     *
     * @return ResponseInterface
     */
    public function getAllContactFields(): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath() . '/fields')
        );
    }

    /**
     * Get a specific Contact Field by ID.
     *
     * @param int $fieldId
     * @return ResponseInterface
     */
    public function getContactField(int $fieldId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath() . '/fields/' . $fieldId)
        );
    }

    /**
     * Create a new Contact Field.
     *
     * @param string $name
     * @param string $dataType
     * @param string $mergeTag
     * @return ResponseInterface
     */
    public function createContactField(string $name, string $dataType, string $mergeTag): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost(
                path: $this->getBasePath() . '/fields',
                body: ['name' => $name, 'data_type' => $dataType, 'merge_tag' => $mergeTag]
            )
        );
    }

    /**
     * Update an existing Contact Field by ID.
     *
     * @param int $fieldId
     * @param string $name
     * @param string $mergeTag
     * @return ResponseInterface
     */
    public function updateContactField(int $fieldId, string $name, string $mergeTag): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPatch(
                path: $this->getBasePath() . '/fields/' . $fieldId,
                body: ['name' => $name, 'merge_tag' => $mergeTag]
            )
        );
    }

    /**
     * Delete a Contact Field by ID.
     *
     * @param int $fieldId
     * @return ResponseInterface
     */
    public function deleteContactField(int $fieldId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpDelete($this->getBasePath() . '/fields/' . $fieldId)
        );
    }

    /**
     * Import contacts in bulk.
     *
     * @param ImportContact[] $contacts
     * @return ResponseInterface
     */
    public function importContacts(array $contacts): ResponseInterface
    {

        return $this->handleResponse(
            $this->httpPost(
                path: $this->getBasePath() . '/imports',
                body: [
                        'contacts' => array_map(
                            function ($contact): array {
                                if (!$contact instanceof ImportContact) {
                                    throw new InvalidArgumentException(
                                        'Each contact must be an instance of ImportContact.'
                                    );
                                }
                                return $contact->toArray();
                            },
                            $contacts
                        )
                    ]
            )
        );
    }

    /**
     * Get the status of a contact import by ID.
     *
     * @param int $importId
     * @return ResponseInterface
     */
    public function getContactImport(int $importId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath() . '/imports/' . $importId)
        );
    }

    /**
     * Create a new Contact Event.
     *
     * @param string $contactIdentifier Contact ID (UUID) or email
     * @param CreateContactEvent $event
     * @return ResponseInterface
     */
    public function createContactEvent(string $contactIdentifier, CreateContactEvent $event): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost(
                path: $this->getBasePath() . '/' . urlencode($contactIdentifier) . '/events',
                body: $event->toArray()
            )
        );
    }

    /**
     * Create a new Contact Export.
     *
     * POST https://mailtrap.io/api/accounts/{account_id}/contacts/exports
     *
     * @param ContactExportFilter[] $filters
     * @return ResponseInterface
     */
    public function createContactExport(array $filters = []): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPost(
                path: $this->getBasePath() . '/exports',
                body: [
                    'filters' => array_map(
                        function ($filter): array {
                            if (!$filter instanceof ContactExportFilter) {
                                throw new InvalidArgumentException('Each filter must be an instance of ContactExportFilter.');
                            }

                            return $filter->toArray();
                        },
                        $filters
                    )
                ]
            )
        );
    }

    /**
     * Get Contact Export status/info by ID.
     *
     * GET https://mailtrap.io/api/accounts/{account_id}/contacts/exports/{export_id}
     *
     * @param int $exportId
     * @return ResponseInterface
     */
    public function getContactExport(int $exportId): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath() . '/exports/' . $exportId)
        );
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    /**
     * Get a Contact by ID or Email.
     *
     * @param string $idOrEmail
     * @return ResponseInterface
     */
    private function getContact(string $idOrEmail): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpGet($this->getBasePath() . '/' . urlencode($idOrEmail))
        );
    }

    /**
     * Update an existing Contact.
     *
     * @param string $contactIdOrEmail
     * @param UpdateContact $contact
     * @return ResponseInterface
     */
    private function updateContact(string $contactIdOrEmail, UpdateContact $contact): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpPatch(
                path: $this->getBasePath() . '/' . urlencode($contactIdOrEmail),
                body: ['contact' => $contact->toArray()]
            )
        );
    }

    /**
     * Delete a Contact by ID or Email.
     *
     * @param string $idOrEmail
     * @return ResponseInterface
     */
    private function deleteContact(string $idOrEmail): ResponseInterface
    {
        return $this->handleResponse(
            $this->httpDelete($this->getBasePath() . '/' . urlencode($idOrEmail))
        );
    }

    private function getBasePath(): string
    {
        return sprintf('%s/api/accounts/%s/contacts', $this->getHost(), $this->getAccountId());
    }
}
