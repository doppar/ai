<?php

namespace Doppar\AI\Tests;

use Doppar\AI\Agent;
use Doppar\AI\MultiAgent;
use Doppar\AI\Tests\Fixtures\InMemoryTestAgent;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Agent\AgentInterface as SymfonyAgentInterface;
use Symfony\AI\Agent\MockAgent;
use Symfony\AI\Agent\MultiAgent\Handoff\Decision;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\ObjectResult;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\AI\Platform\Result\TextResult;

class MultiAgentTest extends TestCase
{
    public function testRoutesToTheHandoffAgentTheOrchestratorSelects(): void
    {
        $billing = new MockAgent(['My invoice is wrong' => 'Let me check your invoice.'], 'billing');
        $support = new MockAgent(['My invoice is wrong' => 'General help here.'], 'support');
        $orchestrator = new RoutingOrchestrator('billing');

        $response = MultiAgent::make($orchestrator)
            ->handoff($billing, when: ['invoice', 'payment'])
            ->handoff($support, when: ['bug', 'help'])
            ->fallback($support)
            ->prompt('My invoice is wrong')
            ->send();

        $this->assertSame('Let me check your invoice.', $response);
        $billing->assertCalledWith('My invoice is wrong');
        $support->assertNotCalled();
    }

    public function testFallsBackWhenTheOrchestratorSelectsNoAgent(): void
    {
        $billing = new MockAgent(['Random question' => 'irrelevant'], 'billing');
        $support = new MockAgent(['Random question' => 'General help here.'], 'support');
        $orchestrator = new RoutingOrchestrator('');

        $response = MultiAgent::make($orchestrator)
            ->handoff($billing, when: ['invoice'])
            ->fallback($support)
            ->prompt('Random question')
            ->send();

        $this->assertSame('General help here.', $response);
        $billing->assertNotCalled();
    }

    public function testExecuteThrowsWithoutAnyHandoffConfigured(): void
    {
        $this->expectException(\RuntimeException::class);

        MultiAgent::make(new RoutingOrchestrator(''))
            ->fallback(new MockAgent())
            ->prompt('hi')
            ->execute();
    }

    public function testExecuteThrowsWithoutAFallbackConfigured(): void
    {
        $this->expectException(\RuntimeException::class);

        MultiAgent::make(new RoutingOrchestrator(''))
            ->handoff(new MockAgent(), when: ['x'])
            ->prompt('hi')
            ->execute();
    }

    public function testAcceptsDoppelAiAgentBuildersNotJustRawSymfonyAgents(): void
    {
        $billing = Agent::make(InMemoryTestAgent::class, 'key')->model('model')->named('billing');
        $support = Agent::make(InMemoryTestAgent::class, 'key')->model('model')->named('support');

        // Resolving via ->toAgent() must not throw, and must preserve the
        // configured name for handoff routing.
        $multi = MultiAgent::make(new RoutingOrchestrator('support'))
            ->handoff($billing, when: ['invoice'])
            ->handoff($support, when: ['bug'])
            ->fallback($support)
            ->prompt('There is a bug in the app');

        $response = $multi->send();

        $this->assertIsString($response);
    }
}

/**
 * A minimal orchestrator stub: when asked for a Decision (structured
 * output), returns one selecting a fixed agent name (or none, for
 * fallback); otherwise answers directly like a normal agent would.
 */
final class RoutingOrchestrator implements SymfonyAgentInterface
{
    public function __construct(private readonly string $decideAgentName)
    {
    }

    public function call(MessageBag $messages, array $options = []): ResultInterface
    {
        if (Decision::class === ($options['response_format'] ?? null)) {
            return new ObjectResult(new Decision($this->decideAgentName));
        }

        return new TextResult('orchestrator direct answer');
    }

    public function getName(): string
    {
        return 'orchestrator';
    }
}
