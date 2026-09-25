<?php

namespace Doppar\AI\Tests\Fixtures;

use Doppar\AI\AgentFactory\Agent\AbstractAgent;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Platform\Test\InMemoryPlatform;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * A provider agent backed by InMemoryPlatform, for exercising
 * AbstractAgent's composition logic (tool-calling / memory / streaming
 * routing) without hitting a real LLM API.
 */
class InMemoryTestAgent extends AbstractAgent
{
    /**
     * The canned result — a string, or a closure receiving (model, input,
     * options) like a real platform call, returning a string or a
     * ResultInterface. See InMemoryPlatform.
     *
     * @var \Closure|string
     */
    public \Closure|string $mockResult = '';

    /**
     * @inheritDoc
     */
    protected function buildPlatform(
        string $key,
        string $model,
        array $config,
        EventDispatcherInterface $eventDispatcher
    ): PlatformInterface {
        return new InMemoryPlatform($this->mockResult);
    }
}
