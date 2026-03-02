<?php

namespace Doppar\AI\AgentFactory\Agent;

use InvalidArgumentException;
use Symfony\AI\Platform\Platform;
use Symfony\AI\Platform\Capability;
use Symfony\AI\Platform\Message\Message;
use Doppar\AI\AgentFactory\AgentInterface;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Bridge\Generic\ModelCatalog;
use Symfony\AI\Platform\Bridge\Generic\PlatformFactory;
use Symfony\AI\Platform\Bridge\Generic\CompletionsModel;

class SelfHost implements AgentInterface
{
    /**
     * The AI platform instance for executing OpenAI calls
     *
     * @var Platform
     */
    private Platform $platform;

    /**
     * A collection of structured messages sent to the model
     *
     * @var MessageBag
     */
    private MessageBag $messages;

    /**
     * Constructor.
     *
     * @param string $key 
     * @param string $model
     * @param array $config
     */
    public function __construct(
        private ?string $key = null,
        private string $model,
        private array $config = []
    ) {
        $modelCatalog = new ModelCatalog([
            $model => [
                'class' => CompletionsModel::class,
                'capabilities' => [
                    Capability::INPUT_MESSAGES,
                    Capability::OUTPUT_TEXT,
                    Capability::OUTPUT_STREAMING,
                    Capability::OUTPUT_STRUCTURED,
                    Capability::INPUT_IMAGE,
                    Capability::TOOL_CALLING,
                ],
            ],
        ]);

        $this->platform = PlatformFactory::create(
            baseUrl: $this->config['host'] ?? '',
            apiKey: $this->key,
            modelCatalog: $modelCatalog
        );
    }

    /**
     * Factory method to create an agent instance.
     *
     * @param string $key
     * @param string $model
     * @param array $config
     * @return AgentInterface
     */
    public static function create(string $key, string $model, $config = []): AgentInterface
    {
        return new self($key, $model, $config);
    }

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
     * Executes the model call using the current messages and parameters.
     *
     * @param array<string, mixed> $params
     * @param bool $complete
     * @param ?string $textInput
     * @return mixed
     */
    public function execute(array $params, bool $complete = false, ?string $textInput = null): mixed
    {
        $result = $this->platform->invoke($this->model, $this->messages, $params);

        return $complete ? $result : $result->asText();
    }

    /**
     * Streams the model response in real-time.
     *
     * This method enables streaming mode and yields text chunks as they arrive
     * from the LLM, allowing for real-time display of the response.
     *
     * @param array<string, mixed> $params Additional parameters (temperature, max_tokens, etc.)
     * @param ?string $textInput Optional text input to override messages
     * @return \Generator<int, string, mixed, void> Yields text chunks as strings
     * @throws \Exception If streaming fails or platform error occurs
     */
    public function stream(array $params, ?string $textInput = null): \Generator
    {
        $params['stream'] = true;

        try {
            $result = $this->platform->invoke(
                $this->model,
                $this->messages,
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
     * @param array<int, array{role: string, content: string}> $data
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
}