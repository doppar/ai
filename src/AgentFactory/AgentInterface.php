<?php

namespace Doppar\AI\AgentFactory;

use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Agent\AgentInterface as SymfonyAgentInterface;
use Symfony\AI\Agent\Memory\MemoryProviderInterface;

interface AgentInterface
{
    /**
     * Factory method to create an agent instance.
     *
     * @param string $key
     * @param string $model
     * @param array $config
     * @return AgentInterface
     */
    public static function create(string $key, string $model, $config = []): AgentInterface;

    /**
     * Sets the internal message collection for the agent.
     *
     * @param array $messages
     * @return mixed
     */
    public function setMessage(array $messages): mixed;

    /**
     * Executes the agent’s logic using provided parameters.
     *
     * @param array $params
     * @param bool $complete
     * @param ?string $textInput
     * @return mixed
     */
    public function execute(array $params, bool $complete = false, ?string $textInput = null): mixed;

    /**
     * Streams the agent's response in real-time using provided parameters.
     *
     * @param array $params
     * @param ?string $textInput
     * @return \Generator
     */
    public function stream(array $params, ?string $textInput = null): \Generator;

    /**
     * Converts raw message data into a MessageBag instance.
     *
     * @param array $data
     * @return MessageBag
     */
    function hydrateMessages(array $data): MessageBag;

    /**
     * Register tool objects (classes carrying #[AsTool] on themselves) the
     * model may call during execute()/stream(). Each
     *
     * @param array<int, object> $tools
     * @return static
     */
    public function withTools(array $tools): static;

    /**
     * Register memory providers whose content is injected into the system
     * prompt before every call, giving the model persistent/contextual
     * information beyond the current message history.
     *
     * @param iterable<MemoryProviderInterface> $memoryProviders
     * @return static
     */
    public function withMemory(iterable $memoryProviders): static;

    /**
     * Request the model's response be constrained to, and deserialized into, the given shape
     *
     * @param string|object|null $responseFormat
     * @return static
     */
    public function asStructured(string|object|null $responseFormat): static;

    /**
     * The agent's name, used to identify it in MultiAgent handoff routing
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set the agent's name (see getName()).
     *
     * @param string $name
     * @return static
     */
    public function named(string $name): static;

    /**
     * Expose the underlying Symfony AI agent
     *
     * @return SymfonyAgentInterface
     */
    public function toAgent(): SymfonyAgentInterface;
}
