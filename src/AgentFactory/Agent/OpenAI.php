<?php

namespace Doppar\AI\AgentFactory\Agent;

use InvalidArgumentException;
use Symfony\AI\Platform\Platform;
use Symfony\AI\Platform\Message\Message;
use Doppar\AI\AgentFactory\AgentInterface;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Bridge\OpenAi\PlatformFactory;

class OpenAI implements AgentInterface
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
     */
    public function __construct(private string $key, private string $model)
    {
        $this->platform = PlatformFactory::create($this->key);
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
        return new self($key, $model);
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
        $result = $this->platform->invoke($this->model, $textInput ?? $this->messages, $params);

        return $complete ? $result : $result->asText();
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