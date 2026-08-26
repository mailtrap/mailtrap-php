<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Domain;

use Mailtrap\Exception\InvalidArgumentException;

/**
 * Class UpdateCompanyInfo
 *
 * All fields are optional. Only the fields provided are updated.
 */
final class UpdateCompanyInfo implements DomainInterface
{
    /**
     * @param string|null $name              Company or individual name
     * @param string|null $address           Street address
     * @param string|null $city              City
     * @param string|null $country           Country
     * @param string|null $zipCode           ZIP or postal code
     * @param string|null $websiteUrl        Company website URL
     * @param string|null $phone             Phone number
     * @param string|null $privacyPolicyUrl  URL to the privacy policy page
     * @param string|null $termsOfServiceUrl URL to the terms of service page
     * @param string|null $infoLevel         One of CompanyInfo::INFO_LEVEL_*
     */
    public function __construct(
        private ?string $name = null,
        private ?string $address = null,
        private ?string $city = null,
        private ?string $country = null,
        private ?string $zipCode = null,
        private ?string $websiteUrl = null,
        private ?string $phone = null,
        private ?string $privacyPolicyUrl = null,
        private ?string $termsOfServiceUrl = null,
        private ?string $infoLevel = null,
    ) {
    }

    public function toArray(): array
    {
        $payload = [];

        if ($this->name !== null) {
            $payload['name'] = $this->name;
        }

        if ($this->address !== null) {
            $payload['address'] = $this->address;
        }

        if ($this->city !== null) {
            $payload['city'] = $this->city;
        }

        if ($this->country !== null) {
            $payload['country'] = $this->country;
        }

        if ($this->zipCode !== null) {
            $payload['zip_code'] = $this->zipCode;
        }

        if ($this->websiteUrl !== null) {
            $payload['website_url'] = $this->websiteUrl;
        }

        if ($this->phone !== null) {
            $payload['phone'] = $this->phone;
        }

        if ($this->privacyPolicyUrl !== null) {
            $payload['privacy_policy_url'] = $this->privacyPolicyUrl;
        }

        if ($this->termsOfServiceUrl !== null) {
            $payload['terms_of_service_url'] = $this->termsOfServiceUrl;
        }

        if ($this->infoLevel !== null) {
            $payload['info_level'] = $this->infoLevel;
        }

        if ($payload === []) {
            throw new InvalidArgumentException(
                'At least one updatable field must be provided to update company info'
            );
        }

        return $payload;
    }
}
