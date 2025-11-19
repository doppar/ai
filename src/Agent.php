<?php

namespace Doppar\AI;

use Doppar\AI\AgentFactory\AgentInterface;

class Agent
{
    /**
     * The fully qualified class name of the agent implementation
     *
     * @var class-string<AgentInterface>
     */
    protected string $agentClass;

    /**
     * The API key used by the agent
     *
     * @var string
     */
    protected string $key;

    /**
     * The model name to be used (e.g., GPT model)
     *
     * @var string
     */
    protected string $model;

    /**
     * Stores all messages (system, user, assistant) to send to the agent
     *
     * @var array<int, array{role: string, content: string}>
     */
    protected array $messages = [];

    /**
     * Additional parameters for the agent execution (temperature, max_tokens, etc.)
     *
     * @var array<string, mixed>
     */
    protected array $params = [];

    /**
     * Indicates whether the agent should use "complete" mode when executing
     *
     * @var bool
     */
    protected bool $complete = false;

    /**
     * Create a new Agent instance
     *
     * @param class-string<AgentInterface> $agentClass
     * @param string $key
     */
    public function __construct(string $agentClass, string $key)
    {
        $this->agentClass = $agentClass;
        $this->key = $key;
    }

    /**
     * Static factory method
     *
     * @param class-string<AgentInterface> $agentClass
     * @param string $key
     */
    public static function make(string $agentClass, string $key): self
    {
        return new self($agentClass, $key);
    }

    /**
     * Static factory with 'using' syntax
     *
     * @param class-string<AgentInterface> $agentClass
     */
    public static function using(string $agentClass): self
    {
        return new self($agentClass, '');
    }

    /**
     * Set the API key
     *
     * @param string $key
     * @return self
     */
    public function withKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Set the model
     *
     * @param string $model
     * @return self
     */
    public function model(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Add a single message
     *
     * @param array $message
     * @return self
     */
    public function message(array $message): self
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * Set multiple messages at once
     *
     * @param array $messages
     * @return self
     */
    public function messages(array $messages): self
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Add a user message
     *
     * @param string $content
     * @return self
     */
    public function prompt(string $content): self
    {
        return $this->message([
            'role' => 'user',
            'content' => $content,
        ]);
    }

    /**
     * Add a system message
     *
     * @param string $content
     * @return self
     */
    public function system(string $content): self
    {
        return $this->message([
            'role' => 'system',
            'content' => $content,
        ]);
    }

    /**
     * Set parameters
     *
     * @param array $params
     * @return self
     */
    public function withParams(array $params): self
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    /**
     * Set max tokens
     *
     * @param int $tokens
     * @return self
     */
    public function maxTokens(int $tokens): self
    {
        $this->params['max_tokens'] = $tokens;

        return $this;
    }

    /**
     * Set temperature
     *
     * @param float $temperature
     * @return self
     */
    public function temperature(float $temperature): self
    {
        $this->params['temperature'] = $temperature;

        return $this;
    }

    /**
     * Enable complete mode
     *
     * @return self
     */
    public function complete(): self
    {
        $this->complete = true;

        return $this;
    }

    /**
     * Execute and get response
     *
     * @return mixed
     */
    public function send(): mixed
    {
        return $this->execute();
    }

    /**
     * Execute the agent
     *
     * @return mixed
     */
    public function execute(): mixed
    {
        return $this->agentClass::create($this->key, $this->model)
            ->setMessage($this->messages)
            ->execute($this->params, $this->complete);
    }
}