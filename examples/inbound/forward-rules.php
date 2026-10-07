<?php

use Mailtrap\Config;
use Mailtrap\DTO\Request\Inbound\CreateInboundForwardRule;
use Mailtrap\DTO\Request\Inbound\InboundForwardRule;
use Mailtrap\DTO\Request\Inbound\InboundForwardRuleCondition;
use Mailtrap\DTO\Request\Inbound\InboundForwardRuleDestination;
use Mailtrap\DTO\Request\Inbound\UpdateInboundForwardRule;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapInboundClient;

require __DIR__ . '/../../vendor/autoload.php';

$config = new Config($_ENV['MAILTRAP_API_KEY']); #your API token from here https://mailtrap.io/api-tokens

$inboxId = (int) ($_ENV['MAILTRAP_INBOUND_INBOX_ID'] ?? 0);
$ruleId = (int) ($_ENV['MAILTRAP_INBOUND_FORWARD_RULE_ID'] ?? 0);

$forwardRules = (new MailtrapInboundClient($config))->forwardRules($inboxId);

/**
 * List the inbox's forward rules.
 *
 * GET https://mailtrap.io/api/inbound/inboxes/{inbox_id}/forward_rules
 */
try {
    $response = $forwardRules->getList();

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

/**
 * Create a forward rule.
 *
 * POST https://mailtrap.io/api/inbound/inboxes/{inbox_id}/forward_rules
 */
try {
    $response = $forwardRules->create(new CreateInboundForwardRule(
        name: 'Copy billing mail to finance',
        conditions: [
            new InboundForwardRuleCondition(
                matchType: InboundForwardRule::MATCH_TYPE_SENDER,
                operator: InboundForwardRule::OPERATOR_ENDS_WITH,
                value: '@billing.example.com'
            ),
        ],
        destinations: [new InboundForwardRuleDestination('finance@example.com')],
    ));

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

/**
 * Get a forward rule by ID.
 *
 * GET https://mailtrap.io/api/inbound/inboxes/{inbox_id}/forward_rules/{id}
 */
try {
    $response = $forwardRules->getById($ruleId);

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

/**
 * Update a forward rule's destinations.
 *
 * PATCH https://mailtrap.io/api/inbound/inboxes/{inbox_id}/forward_rules/{id}
 */
try {
    $response = $forwardRules->update($ruleId, new UpdateInboundForwardRule(
        destinations: [
            new InboundForwardRuleDestination('finance@example.com'),
            new InboundForwardRuleDestination('accounting@example.com'),
        ],
    ));

    var_dump(ResponseHelper::toArray($response));
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

/**
 * Delete a forward rule.
 *
 * DELETE https://mailtrap.io/api/inbound/inboxes/{inbox_id}/forward_rules/{id}
 */
try {
    $response = $forwardRules->delete($ruleId);

    var_dump($response->getStatusCode());
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}
