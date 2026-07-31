<?php

declare(strict_types=1);

namespace Mailtrap\Api;

use Mailtrap\EmailHeader\CategoryHeader;
use Mailtrap\EmailHeader\CustomVariableHeader;
use Mailtrap\EmailHeader\Template\TemplateUuidHeader;
use Mailtrap\EmailHeader\Template\TemplateVariableHeader;
use Mailtrap\Exception\RuntimeException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Header\MailboxListHeader;

/**
 * Maps a Symfony Mime Email to the Mailtrap JSON payload (from/to/cc/bcc,
 * subject/text/html, attachments, category, template and custom variables,
 * custom headers). Shared by the transactional send API (AbstractEmails) and
 * the inbound reply/forward API so both accept the same Email object.
 */
trait EmailPayloadTrait
{
    protected function getPayload(Email $email): array
    {
        $payload = [];

        if (null !== $this->getSender($email->getHeaders())) {
            $payload['from'] = $this->getStringifierAddress($this->getSender($email->getHeaders()));
        }

        if (!empty($this->getRecipients($email->getHeaders(), $email))) {
            $payload['to'] = array_map([$this, 'getStringifierAddress'], $this->getRecipients($email->getHeaders(), $email));
        }

        if (null !== $email->getSubject()) {
            $payload['subject'] = $email->getSubject();
        }

        if (null !== $email->getTextBody()) {
            $payload['text'] = $email->getTextBody();
        }

        if (null !== $email->getHtmlBody()) {
            $payload['html'] = $email->getHtmlBody();
        }

        if ($ccEmails = array_map([$this, 'getStringifierAddress'], $email->getCc())) {
            $payload['cc'] = $ccEmails;
        }

        if ($bccEmails = array_map([$this, 'getStringifierAddress'], $email->getBcc())) {
            $payload['bcc'] = $bccEmails;
        }

        if ($email->getAttachments()) {
            $payload['attachments'] = $this->getAttachments($email);
        }

        $headersToBypass = ['received', 'from', 'to', 'cc', 'bcc', 'subject', 'content-type'];
        foreach ($email->getHeaders()->all() as $name => $header) {
            if (in_array($name, $headersToBypass, true)) {
                continue;
            }

            switch (true) {
                case $header instanceof CustomVariableHeader:
                    $payload[CustomVariableHeader::VAR_NAME][$header->getNameWithoutPrefix()] = $header->getValue();
                    break;
                case $header instanceof TemplateVariableHeader:
                    $payload[TemplateVariableHeader::VAR_NAME][$header->getNameWithoutPrefix()] = $header->getValue();
                    break;
                case $header instanceof CategoryHeader:
                    if (!empty($payload[CategoryHeader::VAR_NAME])) {
                        throw new RuntimeException(
                            sprintf('Too many "%s" instances present in the email headers. Mailtrap does not accept more than 1 category in the email.', CategoryHeader::class)
                        );
                    }

                    $payload[CategoryHeader::VAR_NAME] = $header->getValue();
                    break;
                case $header instanceof TemplateUuidHeader:
                    if (!empty($payload[TemplateUuidHeader::VAR_NAME])) {
                        throw new RuntimeException(
                            sprintf('Too many "%s" instances present in the email headers. Mailtrap does not accept more than 1 template UUID in the email.', TemplateUuidHeader::class)
                        );
                    }

                    $payload[TemplateUuidHeader::VAR_NAME] = $header->getValue();
                    break;
                default:
                    $payload['headers'][$header->getName()] = $header->getBodyAsString();
            }
        }

        return $payload;
    }

    private function getAttachments(Email $email): array
    {
        $attachments = [];
        foreach ($email->getAttachments() as $attachment) {
            $headers = $attachment->getPreparedHeaders();
            $filename = $headers->getHeaderParameter('Content-Disposition', 'filename');
            $disposition = $headers->getHeaderBody('Content-Disposition');

            $att = [
                'content' => str_replace("\r\n", '', $attachment->bodyToString()),
                'type' => $headers->get('Content-Type')->getBody(),
                'filename' => $filename,
                'disposition' => $disposition,
            ];

            if ('inline' === $disposition) {
                $att['content_id'] = $filename;
            }

            $attachments[] = $att;
        }

        return $attachments;
    }

    private function getStringifierAddress(Address $address): array
    {
        $res = ['email' => $address->getAddress()];

        if ($address->getName()) {
            $res['name'] = $address->getName();
        }

        return $res;
    }

    private function getSender(Headers $headers): ?Address
    {
        if ($sender = $headers->get('Sender')) {
            return $sender->getAddress();
        }
        if ($return = $headers->get('Return-Path')) {
            return $return->getAddress();
        }
        if ($from = $headers->get('From')) {
            return $from->getAddresses()[0];
        }

        return null;
    }

    /**
     * @param Headers $headers
     * @param Email   $email
     *
     * @return Address[]
     */
    private function getRecipients(Headers $headers, Email $email): array
    {
        $recipients = [];
        foreach (['to', 'cc', 'bcc'] as $name) {
            foreach ($headers->all($name) as $header) {
                foreach ($header->getAddresses() as $address) {
                    $recipients[] = $address;
                }
            }
        }

        return array_filter(
            $recipients,
            static fn (Address $address) => false === in_array($address, array_merge($email->getCc(), $email->getBcc()), true)
        );
    }

    /**
     * Returns the first address from the 'Reply-To' header, if it exists.
     *
     * @param Headers $headers
     *
     * @return Address|null
     */
    private function getFirstReplyTo(Headers $headers): ?Address
    {
        /** @var MailboxListHeader|null $replyToHeader */
        $replyToHeader = $headers->get('Reply-To');

        if (empty($replyToHeader) || empty($replyToHeader->getAddresses())) {
            return null;
        }

        return $replyToHeader->getAddresses()[0];
    }
}
