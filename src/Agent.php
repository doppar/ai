<?php

namespace Doppar\AI;

use Doppar\AI\AgentFactory\AgentInterface;
use Doppar\AI\Store\StoreInterface;

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
     * Indicates whether the agent should stream the response
     *
     * @var bool
     */
    protected bool $streaming = false;

    /**
     * The host to be used for self host LLM (e.g., GPT model)
     *
     * @var string|null
     */
    protected ?string $host = null;

    /**
     * Store instance for persisting agent state
     *
     * @var StoreInterface|null
     */
    protected ?StoreInterface $store = null;

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
     * Add an assistant message
     *
     * @param string $content
     * @return self
     */
    public function assistant(string $content): self
    {
        return $this->message([
            'role' => 'assistant',
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
        $this->params['max_output_tokens'] = $tokens;

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
     * Enable streaming mode
     *
     * @return self
     */
    public function withStreaming(): self
    {
        $this->streaming = true;

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
        $agent = $this->agentClass::create(
            key: $this->key,
            model: $this->model,
            config: ['host' => $this->host]
        )->setMessage($this->messages);

        if ($this->streaming) {
            return $agent->stream($this->params);
        }

        return $agent->execute($this->params, $this->complete);
    }

    /**
     * Stream the agent response
     *
     * @return \Generator
     */
    public function stream(): \Generator
    {
        $this->streaming = true;
        
        return $this->agentClass::create(
            key: $this->key,
            model: $this->model,
            config: ['host' => $this->host]
        )
            ->setMessage($this->messages)
            ->stream($this->params);
    }

    /**
     * Set the host
     *
     * @param string $host
     * @return self
     */
    public function withHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Set the store instance and save messages with a key
     *
     * @param StoreInterface $store
     * @param string $key
     * @return self
     */
    public function withStore(StoreInterface $store, ?string $key = null): self
    {
        $this->store = $store;

        if ($key !== null) {
            $this->store->store($key, $this->messages);
        }

        return $this;
    }

    /**
     * Store the message history in the configured store
     *
     * @param string $key
     * @param mixed $response
     * @return bool
     */
    public function store(string $key, mixed $response = null): bool
    {
        if ($this->store === null) {
            throw new \Exception('No store configured. Use withStore() to set a store instance.');
        }

        $messagesToStore = $this->messages;

        if ($response !== null) {
            $messagesToStore[] = [
                'role' => 'assistant',
                'content' => $response,
            ];
        }

        return $this->store->store($key, $messagesToStore);
    }

    /**
     * Load the complete message history from the configured store
     *
     * @param string $key
     * @return self
     */
    public function loadMessages(string $key): self
    {
        if ($this->store === null) {
            throw new \Exception('No store configured. Use withStore() to set a store instance.');
        }

        $messages = $this->store->load($key);
        
        if ($messages !== null) {
            $this->messages = $messages;
        }

        return $this;
    }

    /**
     * Embedding
     * 
     * @param string $model
     * param string $content
     */
    public function embedding(string $model, string $content): array
    {
        return $this->agentClass::create(
            key: $this->key,
            model: $model,
            config: ['host' => $this->host]
        )
            ->execute([], true, $content)->asVectors()[0]->getData();
    }

    /**
     * getAgentClass
     */
    public function getAgentClass(): string
    {
        return $this->agentClass;
    }

    /**
     * Translate
     *
     * @param string $languageFrom
     * @param string $languageTo
     * @param string $content
     * @return array|string
     */
    public function translate(string $langFrom, string $langTo, string $content): array|string
    {
        $this->messages = [
            [
                'role' => 'user',
                'content' => 'You are a professional translator. Your task is to translate the text from Language A to Language B faithfully.
                                No interpretation
                                No summarization
                                No commentary
                                Preserve the original formatting
                                Provide only the final translation.
                                Text: ' . $content . '
                                Language A: ' . $langFrom . '
                                Language B: ' . $langTo . '
                                '
            ],
        ];

        return $this->execute();
    }

    /**
     * Translate all files in a folder to another language
     *
     * @param string $langFrom
     * @param string $langTo
     * @return array
     */
    public function translateLocalization(string $langFrom, string $langTo): array
    {
        if (!file_exists(lang_path($langFrom))) {
            throw new \Exception("Folder " . lang_path($langFrom) . " does not exist");
        }
        if (!file_exists(lang_path($langTo))) {
            mkdir(lang_path($langTo), 0755, true);
        }

        $files = glob(lang_path($langFrom) . '/*');
        $results = [];
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $results[] = $this->translate($langFrom, $langTo, $content);
            $results[count($results) - 1] = str_replace(['```php', '```'], '', $results[count($results) - 1]);
            if (strpos($results[count($results) - 1], "\n") === 0) {
                $results[count($results) - 1] = substr($results[count($results) - 1], 1);
            }
            file_put_contents(lang_path($langTo) . DIRECTORY_SEPARATOR . basename($file), $results[count($results) - 1]);
        }

        return $results;
    }
}
