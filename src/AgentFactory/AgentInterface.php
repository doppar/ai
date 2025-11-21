<?php

namespace Doppar\AI\AgentFactory;

use Symfony\AI\Platform\Message\MessageBag;

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
     * Converts raw message data into a MessageBag instance.
     *
     * @param array $data
     * @return MessageBag
     */
    function hydrateMessages(array $data): MessageBag;
}
