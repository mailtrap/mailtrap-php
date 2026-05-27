<?php

declare(strict_types=1);

namespace Mailtrap\Helper;

/**
 * Helpers for working with inbound Mailtrap webhooks.
 *
 * @see https://docs.mailtrap.io/email-api-smtp/advanced/webhooks#verifying-the-signature
 */
final class WebhookSignature
{
    /**
     * Hex-encoded HMAC-SHA256 signature length (SHA-256 produces 32 bytes / 64 hex chars).
     */
    public const SIGNATURE_HEX_LENGTH = 64;

    /**
     * Verifies the HMAC-SHA256 signature of a Mailtrap webhook payload.
     *
     * Mailtrap signs every outbound webhook by computing
     * `HMAC-SHA256(signing_secret, raw_request_body)` and sending the lowercase
     * hex digest in the `Mailtrap-Signature` HTTP header. Compute the same
     * digest on your side and compare it in constant time.
     *
     * The comparison is performed with {@see hash_equals()} to avoid timing
     * side-channels.
     *
     * The method never raises on inputs that could plausibly arrive over the
     * wire (empty strings, wrong-length signatures, non-hex characters, missing
     * secret) — it simply returns `false`. This makes it safe to call directly
     * from a request handler without wrapping in try/catch.
     *
     * @param string $payload       The raw request body, exactly as received.
     *                              **Do not** parse and re-serialize the JSON —
     *                              re-encoding may reorder keys or alter
     *                              whitespace and invalidate the signature.
     * @param string $signature     The value of the `Mailtrap-Signature` HTTP
     *                              header (lowercase hex string).
     * @param string $signingSecret The webhook's `signing_secret`, returned by
     *                              the Webhooks API on webhook creation.
     *
     * @return bool `true` if the signature is valid for the given payload and
     *              secret, `false` otherwise.
     */
    public static function verify(string $payload, string $signature, string $signingSecret): bool
    {
        if ($signature === '' || $signingSecret === '' || $payload === '') {
            return false;
        }

        if (strlen($signature) !== self::SIGNATURE_HEX_LENGTH) {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $signingSecret);

        return hash_equals($expected, $signature);
    }
}
