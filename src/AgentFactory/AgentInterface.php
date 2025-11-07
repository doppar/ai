<?php

namespace Doppar\AI\AgentFactory;

use Symfony\AI\Platform\Message\MessageBag;

interface AgentInterface
{
    public static function create(string $key, string $model): AgentInterface;

    public function setMessage(array $messages): mixed;

    public function execute(array $params, bool $complete = false): mixed;

    function hydrateMessages(array $data): MessageBag;
}
