<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Exception;

use Mailtrap\Exception\HttpServerException;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mailtrap\Exception\HttpServerException
 *
 * Class HttpServerExceptionTest
 */
class HttpServerExceptionTest extends TestCase
{
    /**
     * A server error's JSON `errors` make the exception message, without the generic retry advice; any other body
     * gets the generic message.
     *
     * @dataProvider serverErrorProvider
     */
    public function testCreateFromResponse(Response $response, string $expectedMessage): void
    {
        $exception = HttpServerException::createFromResponse($response);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame($response->getStatusCode(), $exception->getCode());
    }

    public function serverErrorProvider(): iterable
    {
        $genericMessage = 'Internal Server Error. HTTP response code ("%d") received from the API server. '
            . 'Retry later or contact support.';
        $errorsMessage = 'Server error. HTTP response code ("%d") received from the API server. Errors: ';
        $json = ['Content-Type' => 'application/json'];

        yield 'string errors' => [
            new Response(503, $json, json_encode(['errors' => 'Send them again in an update of contact 42.'])),
            sprintf($errorsMessage, 503) . 'Send them again in an update of contact 42.',
        ];
        yield 'array errors' => [
            new Response(500, $json, json_encode(['errors' => ['base' => ['something broke']]])),
            sprintf($errorsMessage, 500) . 'base -> something broke.',
        ];
        yield 'non-string values in errors' => [
            new Response(500, $json, json_encode(['errors' => ['contact_id' => 123, 'base' => null, 'flags' => [true]]])),
            sprintf($errorsMessage, 500) . 'contact_id -> 123. base -> null. flags -> true.',
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
}
