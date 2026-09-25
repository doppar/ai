<?php

namespace Doppar\AI\AgentFactory\Agent;

use Symfony\AI\Platform\Platform;
use Symfony\AI\Platform\Capability;
use Symfony\AI\Platform\Bridge\Generic\ModelCatalog;
use Symfony\AI\Platform\Bridge\Generic\PlatformFactory;
use Symfony\AI\Platform\Bridge\Generic\CompletionsModel;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SelfHost extends AbstractAgent
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

        return PlatformFactory::create(
            baseUrl: $config['host'] ?? '',
            apiKey: $key,
            modelCatalog: $modelCatalog,
            eventDispatcher: $eventDispatcher,
        );
    }
}
