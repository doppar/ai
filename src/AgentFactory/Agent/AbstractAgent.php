<?php

namespace Doppar\AI\AgentFactory\Agent;

use InvalidArgumentException;
use RuntimeException;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\StructuredOutput\PlatformSubscriber;
use Symfony\AI\Agent\Agent as SymfonyAgent;
use Symfony\AI\Agent\AgentInterface as SymfonyAgentInterface;
use Symfony\AI\Agent\Memory\MemoryInputProcessor;
use Symfony\AI\Agent\Toolbox\AgentProcessor;
use Symfony\AI\Agent\Toolbox\Toolbox;
use Doppar\AI\AgentFactory\AgentInterface;
use Doppar\AI\AgentFactory\Processor\ResponseFormatProcessor;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractAgent implements AgentInterface
{
    /**
     * The AI platform instance for executing calls
     *
     * @var PlatformInterface|null
     */
    private ?PlatformInterface $platform = null;

    /**
     * A collection of structured messages sent to the model.
     *
     * @var MessageBag
     */
    protected MessageBag $messages;

    /**
     * Tool objects (each carrying #[AsTool] on its class) the model may
     * call during execute()/stream().
     *
     * @var array<int, object>
     */
    protected array $tools = [];

    /**
     * Memory providers whose content is injected into the system prompt
     * before every call.
     *
     * @var iterable<\Symfony\AI\Agent\Memory\MemoryProviderInterface>
     */
    protected iterable $memoryProviders = [];

    /**
     * The DTO class name or instance the response should be constrained
     * to and deserialized into, or null for plain text.
     *
     * @var string|object|null
     */
    protected string|object|null $responseFormat = null;

    /**
     * This agent's name, used for MultiAgent handoff routing and logging.
     *
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * The Symfony AI agent (platform + wired-up processors) built lazily
     * by toAgent(), memoized until a builder method invalidates it.
     *
     * @var SymfonyAgentInterface|null
     */
    private ?SymfonyAgentInterface $agent = null;

    /**
     * Constructor.
     *
     * Final: create() relies on `new static(...)` to build the right
     * concrete provider, which is only safe as long as no subclass can
     * override the constructor with an incompatible signature.
     *
     * @param string $key
     * @param non-empty-string $model
     * @param array<string, mixed> $config
     */
    final public function __construct(
        protected string $key,
        protected string $model,
        protected array $config = []
    ) {
    }

    /**
     * Factory method to create an agent instance.
     *
     * @param string $key
     * @param string $model
     * @param array<string, mixed> $config
     * @return AgentInterface
     * @throws InvalidArgumentException When $model is empty.
     */
    public static function create(string $key, string $model, $config = []): AgentInterface
    {
        if ('' === $model) {
            throw new InvalidArgumentException('$model must not be empty.');
        }

        return new static($key, $model, $config);
    }

    /**
     * Build this provider's AI Platform.
     *
     * @param string $key
     * @param non-empty-string $model
     * @param array<string, mixed> $config
     * @param EventDispatcherInterface $eventDispatcher
     * @return PlatformInterface
     */
    abstract protected function buildPlatform(
        string $key,
        string $model,
        array $config,
        EventDispatcherInterface $eventDispatcher
    ): PlatformInterface;

    /**
     * Sets and hydrates messages for the model invocation.
     *
     * @param array<int, array{role: string, content: string}> $messages
     *     Array of messages, each containing:
     *     - role: "system"|"user"
     *     - content: string
     * @return $this
     */
    public function setMessage(array $messages): mixed
    {
        $this->messages = $this->hydrateMessages($messages);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withTools(array $tools): static
    {
        $this->tools = $tools;
        $this->agent = null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withMemory(iterable $memoryProviders): static
    {
        $this->memoryProviders = is_array($memoryProviders) ? $memoryProviders : iterator_to_array($memoryProviders);
        $this->agent = null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function asStructured(string|object|null $responseFormat): static
    {
        $this->responseFormat = $responseFormat;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name ?? strtolower((new \ReflectionClass($this))->getShortName());
    }

    /**
     * @inheritDoc
     */
    public function named(string $name): static
    {
        if ('' === $name) {
            throw new RuntimeException('$name must not be empty.');
        }

        $this->name = $name;
        $this->agent = null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toAgent(): SymfonyAgentInterface
    {
        if (null !== $this->agent) {
            return $this->agent;
        }

        // Resolved lazily so toAgent()->call() honours asStructured() just
        // like execute() does, even if it is called after toAgent().
        $inputProcessors = [new ResponseFormatProcessor(fn(): string|object|null => $this->responseFormat)];
        $outputProcessors = [];

        if ([] !== $this->memoryProviders) {
            $inputProcessors[] = new MemoryInputProcessor($this->memoryProviders);
        }

        if ([] !== $this->tools) {
            $toolProcessor = new AgentProcessor(new Toolbox($this->tools));
            $inputProcessors[] = $toolProcessor;
            $outputProcessors[] = $toolProcessor;
        }

        return $this->agent = new SymfonyAgent(
            $this->getPlatform(),
            $this->model,
            $inputProcessors,
            $outputProcessors,
            $this->getName(),
        );
    }

    /**
     * Whether execute()/stream() need to go through the Symfony AI Agent's
     * input/output processor pipeline (required for tool-calling and
     * memory) rather than calling the platform directly.
     *
     * @return bool
     */
    protected function usesAgentPipeline(): bool
    {
        return [] !== $this->tools || [] !== $this->memoryProviders;
    }

    /**
     * Merge the configured structured-output response format into the
     * call parameters, if one was set via asStructured().
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    protected function applyResponseFormat(array $params): array
    {
        if (null !== $this->responseFormat) {
            $params['response_format'] = $this->responseFormat;
        }

        return $params;
    }

    /**
     * Executes the model call using the current messages and parameters.
     *
     * @param array<string, mixed> $params
     * @param bool $complete
     * @param ?string $textInput
     * @return mixed
     */
    public function execute(array $params, bool $complete = false, ?string $textInput = null): mixed
    {
        $params = $this->applyResponseFormat($params);

        // Raw text input (embeddings) is never a chat message, so it never
        // goes through the tool/memory pipeline.
        if (null !== $textInput) {
            $result = $this->getPlatform()->invoke($this->model, $textInput, $params);

            return $complete ? $result : $result->asText();
        }

        if ($this->usesAgentPipeline()) {
            $result = $this->toAgent()->call($this->messages, $params);

            return $complete ? $result : $result->getContent();
        }

        $result = $this->getPlatform()->invoke($this->model, $this->messages, $params);

        if ($complete) {
            return $result;
        }

        return null !== $this->responseFormat ? $result->asObject() : $result->asText();
    }

    /**
     * Streams the model response in real-time.
     *
     * @param array<string, mixed> $params Additional parameters (temperature, max_tokens, etc.)
     * @param ?string $textInput Optional text input to override messages
     * @return \Generator<int, string, mixed, void> Yields text chunks as strings
     * @throws \Exception If streaming fails or platform error occurs
     */
    public function stream(array $params, ?string $textInput = null): \Generator
    {
        $params['stream'] = true;
        $params = $this->applyResponseFormat($params);

        try {
            if (null === $textInput && $this->usesAgentPipeline()) {
                $content = $this->toAgent()->call($this->messages, $params)->getContent();

                // Tool-calling can resolve to a non-streamed result even
                // when streaming was requested (e.g. the final answer
                // after the last tool call); fall back to yielding it
                // whole rather than assuming getContent() is a Generator.
                if (!is_iterable($content)) {
                    if (is_string($content) && '' !== $content) {
                        yield $content;
                    }

                    return;
                }

                foreach ($content as $chunk) {
                    if (!empty($chunk)) {
                        yield $chunk;
                    }
                }

                return;
            }

            $result = $this->getPlatform()->invoke(
                $this->model,
                $textInput ?? $this->messages,
                $params
            );

            foreach ($result->asStream() as $chunk) {
                if (!empty($chunk)) {
                    yield $chunk;
                }
            }
        } catch (\Throwable $e) {
            throw new \Exception(
                "Streaming failed for model {$this->model}: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Converts raw message arrays into a MessageBag.
     *
     * @param array<int, array{role?: string, content?: string}> $data
     * @return MessageBag
     * @throws InvalidArgumentException
     */
    public function hydrateMessages(array $data): MessageBag
    {
        $messages = [];

        foreach ($data as $item) {
            if (!isset($item['role'], $item['content'])) {
                continue;
            }

            switch ($item['role']) {
                case 'system':
                    $messages[] = Message::forSystem($item['content']);
                    break;
                case 'user':
                    $messages[] = Message::ofUser($item['content']);
                    break;
                case 'assistant':
                    $messages[] = Message::ofAssistant($item['content']);
                    break;
                default:
                    throw new InvalidArgumentException("Unknown role : {$item['role']}");
            }
        }

        return new MessageBag(...$messages);
    }

    /**
     * Get (lazily building) this agent's AI Platform.
     *
     * @return PlatformInterface
     */
    protected function getPlatform(): PlatformInterface
    {
        if (null !== $this->platform) {
            return $this->platform;
        }

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new PlatformSubscriber());

        return $this->platform = $this->buildPlatform($this->key, $this->model, $this->config, $dispatcher);
    }
}
