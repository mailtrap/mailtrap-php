<?php

declare(strict_types=1);

namespace Mailtrap\DTO\Request\Domain;

/**
 * Class CreateCompanyInfo
 *
 * Company information for a sending domain. Required for domain compliance
 * verification.
 */
final class CreateCompanyInfo implements DomainInterface
{
    /**
     * @param string      $name              Company or individual name
     * @param string      $address           Street address
     * @param string      $city              City
     * @param string      $country           Country
     * @param string      $zipCode           ZIP or postal code
     * @param string      $websiteUrl        Company website URL
     * @param string|null $phone             Phone number
     * @param string|null $privacyPolicyUrl  URL to the privacy policy page
     * @param string|null $termsOfServiceUrl URL to the terms of service page
     * @param string|null $infoLevel         One of CompanyInfo::INFO_LEVEL_*
     */
    public function __construct(
        private string $name,
        private string $address,
        private string $city,
        private string $country,
        private string $zipCode,
        private string $websiteUrl,
        private ?string $phone = null,
        private ?string $privacyPolicyUrl = null,
        private ?string $termsOfServiceUrl = null,
        private ?string $infoLevel = null,
    ) {
    }

    public function toArray(): array
    {
        $payload = [
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'zip_code' => $this->zipCode,
            'website_url' => $this->websiteUrl,
        ];

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

        return $payload;
    }
}
