<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Sending\EmailLogs;
use Mailtrap\Tests\MailtrapTestCase;
use ReflectionClass;

/**
 * @covers \Mailtrap\Api\AbstractApi
 */
class AbstractApiTest extends MailtrapTestCase
{
    /**
     * Ensures normalizeArrayParams converts numeric array indices to bracket notation in the query string,
     * e.g. filters[category][value][]=Cat1&filters[category][value][]=Cat2, so that APIs (such as Email Logs)
     * that expect multiple values in bracket form receive the correct format.
     */
    public function testNormalizeArrayParamsUsesBracketNotationForMultipleValues(): void
    {
        $params = [
            'filters' => [
                'category' => ['operator' => 'equal', 'value' => ['Cat1', 'Cat2']],
            ],
        ];
        $queryString = $this->normalizeArrayParams($params);

        $this->assertStringNotContainsString('[0]', $queryString, 'Numeric indices must be normalized to bracket notation');
        $this->assertStringNotContainsString('[1]', $queryString, 'Numeric indices must be normalized to bracket notation');
        $this->assertMatchesRegularExpression(
            '/filters%5Bcategory%5D%5Bvalue%5D%5B%5D=Cat1&filters%5Bcategory%5D%5Bvalue%5D%5B%5D=Cat2/',
            $queryString,
            'Query must use filters[category][value][]=Cat1&filters[category][value][]=Cat2'
        );
    }

    /**
     * Ensures top-level list params (such as the marketing stats `sources[]`) are sent as
     * key[]=a&key[]=b, whatever their numeric indices.
     */
    public function testNormalizeArrayParamsUsesBracketNotationForListParams(): void
    {
        $params = [
            'start_date' => '2026-05-01',
            'sources' => ['campaigns', 'automations'],
            'sending_domain_ids' => [3 => 10, 7 => 20],
        ];

        $this->assertSame(
            'start_date=2026-05-01&sources[]=campaigns&sources[]=automations'
            . '&sending_domain_ids[]=10&sending_domain_ids[]=20',
            urldecode($this->normalizeArrayParams($params))
        );
    }

    private function normalizeArrayParams(array $params): string
    {
        $method = (new ReflectionClass(AbstractApi::class))->getMethod('normalizeArrayParams');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        return $method->invoke(new EmailLogs($this->getConfigMock(), self::FAKE_ACCOUNT_ID), $params);
    }
}
