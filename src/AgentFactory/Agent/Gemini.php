<?php

namespace Doppar\AI\AgentFactory\Agent;

use Symfony\AI\Platform\Platform;
use Symfony\AI\Platform\Bridge\Gemini\PlatformFactory;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Gemini extends AbstractAgent
{
    /**
     * @param string $key
     * @param non-empty-string $model
     * @param array<string, mixed> $config
     * @param EventDispatcherInterface $eventDispatcher
     * @return Platform
     */
    protected function buildPlatform(
        string $key,
        string $model,
        array $config,
        EventDispatcherInterface $eventDispatcher
    ): Platform {
        return PlatformFactory::create(
            apiKey: $key,
            eventDispatcher: $eventDispatcher,
        );
    }
}
