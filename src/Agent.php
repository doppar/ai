<?php

namespace Doppar\AI;

use Doppar\AI\AgentFactory\AgentInterface;

class Agent
{
    /**
     * @param class-string<AgentInterface> $agent
     * @param string $key
     * @param string $model
     * @param array $datas
     * @param array $params
     * @param bool $complete
     */
    public static function run(string $agent, string $key, string $model, array $datas, array $params, bool $complete = false): mixed
    {
        return $agent::create($key, $model)
            ->setMessage($datas)
            ->execute($params, $complete);
    }
}