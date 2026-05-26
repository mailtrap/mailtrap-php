<?php

declare(strict_types=1);

use Mailtrap\Helper\WebhookSignature;

require __DIR__ . '/../../vendor/autoload.php';

$signingSecret = getenv('MAILTRAP_WEBHOOK_SIGNING_SECRET');

// Use the raw request body — parsing and re-serializing the JSON may
// reorder keys or alter whitespace and invalidate the signature.
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_MAILTRAP_SIGNATURE'] ?? '';

if (!WebhookSignature::verify($payload !== false ? $payload : '', $signature, $signingSecret)) {
    http_response_code(401);
    echo 'Invalid signature';
    return;
}

http_response_code(200);
