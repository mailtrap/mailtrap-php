<?php

declare(strict_types=1);

use Mailtrap\Helper\WebhookSignature;

require __DIR__ . '/../../vendor/autoload.php';

// --- Direct verification (e.g. for unit tests or custom routers) ----------
$payload = '{"event":"delivery","message_id":"abc-123"}';
$signingSecret = '8d9a3c0e7f5b2d4a6c1e9f8b3a7d5c2e';
$signature = hash_hmac('sha256', $payload, $signingSecret);

assert(WebhookSignature::verify($payload, $signature, $signingSecret) === true);

// Bad input never raises — it returns false:
assert(WebhookSignature::verify($payload, 'not-hex', $signingSecret) === false);
assert(WebhookSignature::verify($payload, '', $signingSecret) === false);
