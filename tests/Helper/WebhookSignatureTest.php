<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Helper;

use Mailtrap\Helper\WebhookSignature;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mailtrap\Helper\WebhookSignature
 *
 * Class WebhookSignatureTest
 */
class WebhookSignatureTest extends TestCase
{
    // -----------------------------------------------------------------------
    // Cross-SDK fixture
    //
    // The (payload, signing_secret, expected_signature) triple below is the
    // canonical fixture shared verbatim by every official Mailtrap SDK
    // (mailtrap-ruby, mailtrap-python, mailtrap-php, mailtrap-nodejs,
    // mailtrap-java, mailtrap-dotnet). Any change here MUST be mirrored in
    // the equivalent test files in the other SDKs so the helpers stay
    // byte-for-byte compatible across languages.
    // -----------------------------------------------------------------------
    private const FIXTURE_PAYLOAD = '{"event":"delivery","sending_stream":"transactional","category":"welcome","message_id":"a8b1d8f6-1f8d-4a3c-9b2e-1a2b3c4d5e6f","email":"recipient@example.com","event_id":"f1e2d3c4-b5a6-7890-1234-567890abcdef","timestamp":1716070000}';
    private const FIXTURE_SIGNING_SECRET = '8d9a3c0e7f5b2d4a6c1e9f8b3a7d5c2e';
    private const FIXTURE_EXPECTED_SIGNATURE = '6d262e2611cd09be1f948382b5c611d63b0e585c4c9c5e40139d6ac3876d5433';

    // --- 1. Valid signature for given payload + secret ---------------------
    public function testReturnsTrueForValidSignaturePayloadAndSecret(): void
    {
        $this->assertTrue(
            WebhookSignature::verify(
                self::FIXTURE_PAYLOAD,
                self::FIXTURE_EXPECTED_SIGNATURE,
                self::FIXTURE_SIGNING_SECRET
            )
        );
    }

    // --- 2. Wrong secret ---------------------------------------------------
    public function testReturnsFalseWithWrongSigningSecret(): void
    {
        $this->assertFalse(
            WebhookSignature::verify(
                self::FIXTURE_PAYLOAD,
                self::FIXTURE_EXPECTED_SIGNATURE,
                'ffffffffffffffffffffffffffffffff'
            )
        );
    }

    // --- 3. Payload tampered (one byte changed) ----------------------------
    public function testReturnsFalseWhenPayloadIsTampered(): void
    {
        $tampered = str_replace('delivery', 'Delivery', self::FIXTURE_PAYLOAD);

        $this->assertFalse(
            WebhookSignature::verify(
                $tampered,
                self::FIXTURE_EXPECTED_SIGNATURE,
                self::FIXTURE_SIGNING_SECRET
            )
        );
    }

    // --- 4. Signature with wrong length ------------------------------------
    public function testReturnsFalseWithoutRaisingWhenSignatureTooShort(): void
    {
        $tooShort = substr(self::FIXTURE_EXPECTED_SIGNATURE, 0, 31);

        $this->assertFalse(
            WebhookSignature::verify(
                self::FIXTURE_PAYLOAD,
                $tooShort,
                self::FIXTURE_SIGNING_SECRET
            )
        );
    }

    // --- 5. Signature with non-hex characters ------------------------------
    public function testReturnsFalseWithoutRaisingForNonHexSignature(): void
    {
        $notHex = str_repeat('z', WebhookSignature::SIGNATURE_HEX_LENGTH);

        $this->assertFalse(
            WebhookSignature::verify(
                self::FIXTURE_PAYLOAD,
                $notHex,
                self::FIXTURE_SIGNING_SECRET
            )
        );
    }

    // --- 6. Empty signature string -----------------------------------------
    public function testReturnsFalseForEmptySignature(): void
    {
        $this->assertFalse(
            WebhookSignature::verify(
                self::FIXTURE_PAYLOAD,
                '',
                self::FIXTURE_SIGNING_SECRET
            )
        );
    }

    // --- 7. Empty signing_secret -------------------------------------------
    public function testReturnsFalseForEmptySigningSecret(): void
    {
        $this->assertFalse(
            WebhookSignature::verify(
                self::FIXTURE_PAYLOAD,
                self::FIXTURE_EXPECTED_SIGNATURE,
                ''
            )
        );
    }

    // --- 8. Empty payload + non-empty signature ----------------------------
    public function testReturnsFalseForEmptyPayload(): void
    {
        $this->assertFalse(
            WebhookSignature::verify(
                '',
                self::FIXTURE_EXPECTED_SIGNATURE,
                self::FIXTURE_SIGNING_SECRET
            )
        );
    }

    // --- 9. Known-good cross-SDK fixture -----------------------------------
    public function testMatchesHardcodedHmacSha256DigestForSharedFixture(): void
    {
        // Recompute the digest in-place so a regression in PHP's hash
        // extension or the fixture itself fails loudly: this is the
        // byte-for-byte contract every other Mailtrap SDK must satisfy.
        $computed = hash_hmac('sha256', self::FIXTURE_PAYLOAD, self::FIXTURE_SIGNING_SECRET);

        $this->assertSame(self::FIXTURE_EXPECTED_SIGNATURE, $computed);
        $this->assertTrue(
            WebhookSignature::verify(
                self::FIXTURE_PAYLOAD,
                self::FIXTURE_EXPECTED_SIGNATURE,
                self::FIXTURE_SIGNING_SECRET
            )
        );
    }
}
