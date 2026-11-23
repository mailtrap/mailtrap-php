<?php

declare(strict_types=1);

namespace Mailtrap\Exception;

use JsonException;
use Mailtrap\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface;

/**
 * Class HttpClientException
 */
class HttpClientException extends HttpException
{
    public const ERROR_PREFIXES = [
        400 => 'Bad request. Fix errors listed in response before retrying.',
        401 => 'Unauthorized. Make sure you are sending correct credentials with the request before retrying.',
        403 => 'Forbidden. Make sure domain verification process is completed or check your permissions.',
        404 => 'The requested entity has not been found.',
    ];

    public static function createFromResponse(ResponseInterface $response): HttpClientException
    {
        $errorMsg = '';
        $statusCode = $response->getStatusCode();

        try {
            $body = ResponseHelper::toArray($response);
        } catch (JsonException|InvalidTypeException) {
            $body['error'] = $response->getBody()->__toString();
            if (empty($body['error'])) {
                $body['error'] = 'No error info in the response body';
            }
        }

        if (isset(self::ERROR_PREFIXES[$statusCode])) {
            $errorMsg .= self::ERROR_PREFIXES[$statusCode] . ' ';
        }

        $errorMsg .= trim('Errors: ' . self::getErrorMsg(!empty($body['errors']) ? $body['errors'] : $body['error']));

        return new self (
            !empty($errorMsg)
                ? $errorMsg
                : sprintf('HTTP response code ("%d") received from the API server (no error info)', $statusCode),
            $statusCode
        );
    }

    /**
     * It can be different structure of errors in the response...
     *
     * Examples:
     * {"errors": ["'to' address is required", "'subject' is required"]}    400 errorS (array)
     * {"error": "Incorrect API token"}                                     401 error  (string)
     * {"errors": "Access forbidden"}                                       403 errorS (string)
     * {"error": "Not found"}                                               404 error  (string)
     * {"errors": {"name":["is too short (minimum is 2 characters)"]}}      422 errorS (array with key name)
     *
     *
     * @param array|string $errors
     *
     * @return string
     */
    public static function getErrorMsg(array|string $errors): string
    {
        $errorMsg = '';
        if (is_array($errors)) {
            foreach ($errors as $key => $value) {
                if (is_string($key)) {
                    // add name of field
                    $errorMsg .= $key . ' -> ';
                }

                // a number, boolean or null is written out as JSON
                $errorMsg .= self::getErrorMsg(is_array($value) || is_string($value) ? $value : json_encode($value));
            }
        } else {
            $errorMsg .= $errors . '. ';
        }

        return $errorMsg;
    }
}
