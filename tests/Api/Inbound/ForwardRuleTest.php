<?php

declare(strict_types=1);

namespace Mailtrap\Tests\Api\Inbound;

use Mailtrap\Api\AbstractApi;
use Mailtrap\Api\Inbound\ForwardRule as ForwardRuleApi;
use Mailtrap\DTO\Request\Inbound\CreateInboundForwardRule;
use Mailtrap\DTO\Request\Inbound\InboundForwardRule;
use Mailtrap\DTO\Request\Inbound\InboundForwardRuleCondition;
use Mailtrap\DTO\Request\Inbound\InboundForwardRuleDestination;
use Mailtrap\DTO\Request\Inbound\UpdateInboundForwardRule;
use Mailtrap\Exception\InvalidArgumentException;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\Tests\MailtrapTestCase;
use Nyholm\Psr7\Response;

/**
 * @covers \Mailtrap\Api\Inbound\ForwardRule
 *
 * Class ForwardRuleTest
 */
class ForwardRuleTest extends MailtrapTestCase
{
    private const FAKE_RULE_ID = 7;
    private const BASE_URL = AbstractApi::DEFAULT_HOST . '/api/inbound/inboxes/' . self::FAKE_INBOX_ID . '/forward_rules';

    private ?ForwardRuleApi $forwardRule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->forwardRule = $this->getMockBuilder(ForwardRuleApi::class)
            ->onlyMethods(['httpGet', 'httpPost', 'httpPatch', 'httpDelete'])
            ->setConstructorArgs([$this->getConfigMock(), self::FAKE_INBOX_ID])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->forwardRule = null;
        parent::tearDown();
    }

    public function testGetList(): void
    {
        $this->forwardRule->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_URL)
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['data' => [$this->getRuleData(), ['id' => 8, 'name' => 'Archive', 'conditions' => [], 'destinations' => [['email' => 'archive@example.com']]]]])
            ));

        $data = ResponseHelper::toArray($this->forwardRule->getList());

        $this->assertCount(2, $data['data']);
        $this->assertSame(self::FAKE_RULE_ID, $data['data'][0]['id']);
        $this->assertSame('ends_with', $data['data'][0]['conditions'][0]['operator']);
        $this->assertSame([], $data['data'][1]['conditions']);
    }

    public function testGetById(): void
    {
        $this->forwardRule->expects($this->once())
            ->method('httpGet')
            ->with(self::BASE_URL . '/' . self::FAKE_RULE_ID)
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['data' => $this->getRuleData()])
            ));

        $data = ResponseHelper::toArray($this->forwardRule->getById(self::FAKE_RULE_ID));

        $this->assertSame('Copy billing mail to finance', $data['data']['name']);
        $this->assertSame('finance@example.com', $data['data']['destinations'][0]['email']);
    }

    public function testCreateSendsFlatBody(): void
    {
        $this->forwardRule->expects($this->once())
            ->method('httpPost')
            ->with(self::BASE_URL, [], [
                'name' => 'Escalate urgent tickets',
                'conditions' => [
                    ['match_type' => 'sender', 'operator' => 'ends_with', 'value' => '@billing.example.com'],
                    ['match_type' => 'header', 'operator' => 'equal', 'value' => 'high', 'header_key' => 'X-Priority-Level'],
                    ['match_type' => 'header', 'operator' => 'not_empty', 'header_key' => 'X-Ticket-Id'],
                ],
                'destinations' => [
                    ['email' => 'oncall@example.com'],
                ],
            ])
            ->willReturn(new Response(
                201,
                ['Content-Type' => 'application/json'],
                json_encode(['data' => $this->getRuleData()])
            ));

        $response = $this->forwardRule->create(new CreateInboundForwardRule(
            name: 'Escalate urgent tickets',
            conditions: [
                new InboundForwardRuleCondition(
                    InboundForwardRule::MATCH_TYPE_SENDER,
                    InboundForwardRule::OPERATOR_ENDS_WITH,
                    '@billing.example.com'
                ),
                new InboundForwardRuleCondition(
                    matchType: InboundForwardRule::MATCH_TYPE_HEADER,
                    operator: InboundForwardRule::OPERATOR_EQUAL,
                    value: 'high',
                    headerKey: 'X-Priority-Level'
                ),
                new InboundForwardRuleCondition(
                    matchType: InboundForwardRule::MATCH_TYPE_HEADER,
                    operator: InboundForwardRule::OPERATOR_NOT_EMPTY,
                    headerKey: 'X-Ticket-Id'
                ),
            ],
            destinations: [new InboundForwardRuleDestination('oncall@example.com')],
        ));

        $this->assertSame(201, $response->getStatusCode());
    }

    public function testCreateWithNameOnlyOmitsConditionsAndDestinations(): void
    {
        $this->forwardRule->expects($this->once())
            ->method('httpPost')
            ->with(self::BASE_URL, [], ['name' => 'Catch all'])
            ->willReturn(new Response(201, ['Content-Type' => 'application/json'], json_encode(['data' => $this->getRuleData()])));

        $this->forwardRule->create(new CreateInboundForwardRule('Catch all'));
    }

    public function testUpdateSendsOnlyProvidedFields(): void
    {
        $this->forwardRule->expects($this->once())
            ->method('httpPatch')
            ->with(self::BASE_URL . '/' . self::FAKE_RULE_ID, [], [
                'destinations' => [
                    ['email' => 'finance@example.com'],
                    ['email' => 'accounting@example.com'],
                ],
            ])
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['data' => $this->getRuleData()])
            ));

        $this->forwardRule->update(self::FAKE_RULE_ID, new UpdateInboundForwardRule(
            destinations: [
                new InboundForwardRuleDestination('finance@example.com'),
                new InboundForwardRuleDestination('accounting@example.com'),
            ],
        ));
    }

    public function testUpdateSendsEmptyArrayToClearConditions(): void
    {
        $this->forwardRule->expects($this->once())
            ->method('httpPatch')
            ->with(self::BASE_URL . '/' . self::FAKE_RULE_ID, [], ['name' => 'Renamed', 'conditions' => []])
            ->willReturn(new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['data' => $this->getRuleData()])
            ));

        $this->forwardRule->update(
            self::FAKE_RULE_ID,
            new UpdateInboundForwardRule(name: 'Renamed', conditions: [])
        );
    }

    public function testUpdateWithoutFieldsThrows(): void
    {
        $this->forwardRule->expects($this->never())->method('httpPatch');

        $this->expectException(InvalidArgumentException::class);

        $this->forwardRule->update(self::FAKE_RULE_ID, new UpdateInboundForwardRule());
    }

    public function testDelete(): void
    {
        $this->forwardRule->expects($this->once())
            ->method('httpDelete')
            ->with(self::BASE_URL . '/' . self::FAKE_RULE_ID)
            ->willReturn(new Response(204));

        $this->assertSame(204, $this->forwardRule->delete(self::FAKE_RULE_ID)->getStatusCode());
    }

    public function testConditionRejectsUnknownMatchType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"matchType" must be one of');

        new InboundForwardRuleCondition('subject', InboundForwardRule::OPERATOR_EQUAL, 'x');
    }

    public function testConditionRejectsUnknownOperator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"operator" must be one of');

        new InboundForwardRuleCondition(InboundForwardRule::MATCH_TYPE_SENDER, 'matches', 'x');
    }

    private function getRuleData(): array
    {
        return [
            'id' => self::FAKE_RULE_ID,
            'name' => 'Copy billing mail to finance',
            'created_at' => '2026-05-08T10:30:00.000Z',
            'updated_at' => '2026-05-08T10:30:00.000Z',
            'conditions' => [
                ['match_type' => 'sender', 'operator' => 'ends_with', 'value' => '@billing.example.com', 'header_key' => null],
            ],
            'destinations' => [
                ['email' => 'finance@example.com'],
            ],
        ];
    }
}
