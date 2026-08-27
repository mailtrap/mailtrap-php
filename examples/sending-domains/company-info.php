<?php

declare(strict_types=1);

use Mailtrap\Config;
use Mailtrap\DTO\Request\Domain\CompanyInfo;
use Mailtrap\DTO\Request\Domain\CreateCompanyInfo;
use Mailtrap\DTO\Request\Domain\UpdateCompanyInfo;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapSendingClient;

require __DIR__ . '/../../vendor/autoload.php';

$domainId = (int) $_ENV['MAILTRAP_DOMAIN_ID'];
$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens

$companyInfo = (new MailtrapSendingClient($config))->companyInfo($domainId); #required parameter is domainId

/**
 * Create company info for a sending domain
 *
 * POST https://mailtrap.io/api/domains/{domain_id}/company_info
 */
try {
    $response = $companyInfo->createCompanyInfo(
        new CreateCompanyInfo(
            name: 'Mailtrap',
            address: '123 Main St',
            city: 'San Francisco',
            country: 'US',
            zipCode: '94105',
            websiteUrl: 'https://mailtrap.io',
            phone: '+1-555-0100',
            privacyPolicyUrl: 'https://mailtrap.io/privacy',
            termsOfServiceUrl: 'https://mailtrap.io/terms',
            infoLevel: CompanyInfo::INFO_LEVEL_BUSINESS
        )
    );

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Get company info for a sending domain
 *
 * GET https://mailtrap.io/api/domains/{domain_id}/company_info
 */
try {
    $response = $companyInfo->getCompanyInfo();

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}


/**
 * Update company info for a sending domain
 *
 * PATCH https://mailtrap.io/api/domains/{domain_id}/company_info
 *
 * Only the fields provided are updated.
 */
try {
    $response = $companyInfo->updateCompanyInfo(new UpdateCompanyInfo(city: 'New York', zipCode: '10001'));

    // print the response body (array)
    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
