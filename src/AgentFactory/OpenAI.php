<?php

namespace Doppar\AI\AgentFactory;

use InvalidArgumentException;
use Symfony\AI\Platform\Platform;
use Symfony\AI\Platform\Message\Message;
use Doppar\AI\AgentFactory\AgentInterface;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Bridge\OpenAi\PlatformFactory;

class OpenAI implements AgentInterface
{
    private Platform $platform;
    private MessageBag $messages;

    public static function create(string $key, string $model): AgentInterface
    {
        return new self($key, $model);
    }

    public function __construct(private string $key, private string $model)
    {
        $this->platform = PlatformFactory::create($this->key);
    }

    public function setMessage(array $messages): mixed
    {
        $this->messages = $this->hydrateMessages($messages);
        return $this;
    }

    public function execute(array $params, bool $complete = false): mixed
    {
        $result = $this->platform->invoke($this->model, $this->messages, $params);
        return $complete ? $result : $result->asText();
    }

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
                default:
                    throw new InvalidArgumentException("Unknown role : {$item['role']}");
            }
        }

        return new MessageBag(...$messages);
    }
}