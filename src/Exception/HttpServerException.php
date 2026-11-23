<?php

declare(strict_types=1);

namespace Mailtrap\Exception;

use JsonException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class HttpServerException
 */
class HttpServerException extends HttpException
{
    /**
     * Appends the `errors` of a JSON body, such as the contact named by a contact create or update
     * that saved the contact but not its subscription topics, to the generic server error message.
     */
    public static function createFromResponse(ResponseInterface $response): HttpServerException
    {
        $statusCode = $response->getStatusCode();
        $errorMsg = sprintf(
            'Internal Server Error. HTTP response code ("%d") received from the API server. Retry later or contact support.',
            $statusCode
        );

        $errors = self::getResponseErrors($response);
        if ($errors !== '') {
            $errorMsg .= ' Errors: ' . $errors;
        }

        return new self($errorMsg, $statusCode);
    }

    /**
     * The body's `errors` as text, or '' when the body isn't JSON or has none (a proxy's HTML page, say).
     */
    private static function getResponseErrors(ResponseInterface $response): string
    {
        try {
            $body = json_decode($response->getBody()->__toString(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return '';
        }

        $errors = is_array($body) ? $body['errors'] ?? null : null;

        if (is_array($errors)) {
            // getErrorMsg takes only strings and arrays, so a number, boolean or null inside would be a TypeError
            array_walk_recursive($errors, static function (mixed &$value): void {
                if (!is_string($value)) {
                    $value = json_encode($value);
                }
            });
        }

        return match (true) {
            is_string($errors) => trim($errors),
            is_array($errors) => trim(HttpClientException::getErrorMsg($errors)),
            default => '',
        };
    }
}
