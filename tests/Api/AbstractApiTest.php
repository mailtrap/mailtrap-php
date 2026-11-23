<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Sending\EmailLogs;
use Mailtrap\Exception\HttpServerException;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;
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

    /**
     * A server error's JSON `errors` reaches the exception message; any other body leaves the generic message as is.
     *
     * @dataProvider serverErrorProvider
     */
    public function testHandleResponseAddsServerErrorBodyToMessage(Response $response, string $expectedMessage): void
    {
        try {
            $this->handleResponse($response);
            $this->fail('HttpServerException expected');
        } catch (HttpServerException $e) {
            $this->assertSame($expectedMessage, $e->getMessage());
            $this->assertSame($response->getStatusCode(), $e->getCode());
        }
    }

    public function serverErrorProvider(): iterable
    {
        $genericMessage = 'Internal Server Error. HTTP response code ("%d") received from the API server. '
            . 'Retry later or contact support.';
        $json = ['Content-Type' => 'application/json'];

        yield 'string errors' => [
            new Response(503, $json, json_encode(['errors' => 'Send them again in an update of contact 42.'])),
            sprintf($genericMessage, 503) . ' Errors: Send them again in an update of contact 42.',
        ];
        yield 'array errors' => [
            new Response(500, $json, json_encode(['errors' => ['base' => ['something broke']]])),
            sprintf($genericMessage, 500) . ' Errors: base -> something broke.',
        ];
        yield 'non-string values in errors' => [
            new Response(500, $json, json_encode(['errors' => ['contact_id' => 123, 'base' => null, 'flags' => [true]]])),
            sprintf($genericMessage, 500) . ' Errors: contact_id -> 123. base -> null. flags -> true.',
        ];
        yield 'html body' => [
            new Response(502, ['Content-Type' => 'text/html'], '<html><body>Bad Gateway</body></html>'),
            sprintf($genericMessage, 502),
        ];
        yield 'empty body' => [new Response(504), sprintf($genericMessage, 504)];
        yield 'json without errors' => [
            new Response(500, $json, json_encode(['error' => 'x'])),
            sprintf($genericMessage, 500),
        ];
        yield 'json scalar' => [new Response(500, $json, json_encode('oops')), sprintf($genericMessage, 500)];
    }

    private function handleResponse(Response $response): void
    {
        $method = (new ReflectionClass(AbstractApi::class))->getMethod('handleResponse');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $method->invoke(new EmailLogs($this->getConfigMock(), self::FAKE_ACCOUNT_ID), $response);
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
