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
     * The host to be used for self host LLM (e.g., GPT model)
     *
     * @var string|null
     */
    protected ?string $host = null;

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
        return $this->agentClass::create(
            key: $this->key,
            model: $this->model,
            config: ['host' => $this->host]
        )
            ->setMessage($this->messages)
            ->execute($this->params, $this->complete);
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
        if (!file_exists(base_path() . "/lang/$langFrom")) {
            throw new \Exception("Folder " . base_path() . "/lang/$langFrom does not exist");
        }
        if (!file_exists(base_path() . "/lang/$langTo")) {
            mkdir(base_path() . "/lang/$langTo");
        }

        $files = glob(base_path() . "/lang/$langFrom/*");
        $results = [];
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $results[] = $this->translate($langFrom, $langTo, $content);
            $results[count($results) - 1] = str_replace(['```php', '```'], '', $results[count($results) - 1]);
            if (strpos($results[count($results) - 1], "\n") === 0) {
                $results[count($results) - 1] = substr($results[count($results) - 1], 1);
            }
            file_put_contents(base_path() . "/lang/$langTo/" . basename($file), $results[count($results) - 1]);
        }

        return $results;
    }
}
