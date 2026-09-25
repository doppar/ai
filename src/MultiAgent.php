<?php

namespace Doppar\AI;

use RuntimeException;
use Symfony\AI\Agent\AgentInterface as SymfonyAgentInterface;
use Symfony\AI\Agent\MultiAgent\Handoff;
use Symfony\AI\Agent\MultiAgent\MultiAgent as SymfonyMultiAgent;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\ResultInterface;

/**
 * Fluent wrapper around Symfony AI's MultiAgent/Handoff: routes a prompt to
 * one of several specialized agents based on keyword conditions, with a
 * fallback agent for anything that doesn't match.
 *
 * The orchestrator itself makes the routing decision (via structured
 * output), then the selected agent answers the original prompt — so the
 * orchestrator's underlying provider must support structured output,
 * which every Doppar\AI\Agent-built agent does by default.
 *
 * Usage:
 *   $billing = Agent::make(Claude::class, $key)->model('claude-...')->named('billing')
 *       ->system('You handle invoices, payments and refunds.');
 *   $support = Agent::make(Claude::class, $key)->model('claude-...')->named('support')
 *       ->system('You handle bugs and general help requests.');
 *
 *   $response = MultiAgent::make($billing)
 *       ->handoff($billing, when: ['invoice', 'payment', 'refund'])
 *       ->handoff($support, when: ['bug', 'error', 'help'])
 *       ->fallback($support)
 *       ->prompt('My invoice total looks wrong')
 *       ->send();
 */
class MultiAgent
{
    /**
     * @var array<int, Handoff>
     */
    protected array $handoffs = [];

    /**
     * @var SymfonyAgentInterface|null
     */
    protected ?SymfonyAgentInterface $fallbackAgent = null;

    /**
     * @var array<int, \Symfony\AI\Platform\Message\MessageInterface>
     */
    protected array $messages = [];

    /**
     * @var array<string, mixed>
     */
    protected array $params = [];

    /**
     * @var non-empty-string
     */
    protected string $name = 'multi-agent';

    /**
     * @param SymfonyAgentInterface|Agent $orchestrator The agent responsible for deciding which specialist to hand off to.
     */
    public function __construct(protected SymfonyAgentInterface|Agent $orchestrator)
    {
    }

    /**
     * Static factory method.
     *
     * @param SymfonyAgentInterface|Agent $orchestrator
     * @return self
     */
    public static function make(SymfonyAgentInterface|Agent $orchestrator): self
    {
        return new self($orchestrator);
    }

    /**
     * Route to $agent whenever the prompt contains any of the $when
     * keywords/phrases.
     *
     * @param SymfonyAgentInterface|Agent $agent
     * @param array<int, string> $when
     * @return self
     */
    public function handoff(SymfonyAgentInterface|Agent $agent, array $when): self
    {
        $this->handoffs[] = new Handoff($this->resolve($agent), $when);

        return $this;
    }

    /**
     * The agent used when no handoff condition matches.
     *
     * @param SymfonyAgentInterface|Agent $agent
     * @return self
     */
    public function fallback(SymfonyAgentInterface|Agent $agent): self
    {
        $this->fallbackAgent = $this->resolve($agent);

        return $this;
    }

    /**
     * Name this multi-agent, used for logging.
     *
     * @param string $name
     * @return self
     * @throws RuntimeException When $name is empty.
     */
    public function named(string $name): self
    {
        if ('' === $name) {
            throw new RuntimeException('$name must not be empty.');
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Add the user prompt to route.
     *
     * @param string $content
     * @return self
     */
    public function prompt(string $content): self
    {
        $this->messages[] = Message::ofUser($content);

        return $this;
    }

    /**
     * Set additional parameters (temperature, max_tokens, etc.) forwarded
     * to whichever agent handles the prompt.
     *
     * @param array<string, mixed> $params
     * @return self
     */
    public function withParams(array $params): self
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    /**
     * Route and execute, returning the selected agent's response content.
     *
     * @return mixed
     */
    public function send(): mixed
    {
        return $this->execute()->getContent();
    }

    /**
     * Route and execute, returning the full result.
     *
     * @return ResultInterface
     * @throws RuntimeException When no handoff or no fallback has been configured.
     */
    public function execute(): ResultInterface
    {
        if ([] === $this->handoffs) {
            throw new RuntimeException('At least one handoff() must be configured before calling send()/execute().');
        }

        if (null === $this->fallbackAgent) {
            throw new RuntimeException('A fallback() agent must be configured before calling send()/execute().');
        }

        $multi = new SymfonyMultiAgent(
            $this->resolve($this->orchestrator),
            $this->handoffs,
            $this->fallbackAgent,
            $this->name,
        );

        return $multi->call(new MessageBag(...$this->messages), $this->params);
    }

    /**
     * @param SymfonyAgentInterface|Agent $agent
     * @return SymfonyAgentInterface
     */
    private function resolve(SymfonyAgentInterface|Agent $agent): SymfonyAgentInterface
    {
        return $agent instanceof Agent ? $agent->toAgent() : $agent;
    }
}
