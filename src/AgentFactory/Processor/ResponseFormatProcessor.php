<?php

namespace Doppar\AI\AgentFactory\Processor;

use Symfony\AI\Agent\Input;
use Symfony\AI\Agent\InputProcessorInterface;

/**
 * Applies the structured-output response format set via asStructured() to
 * calls made through the Symfony AI agent (toAgent()->call()), so they
 * behave the same as execute().
 */
final class ResponseFormatProcessor implements InputProcessorInterface
{
    /**
     * @param \Closure(): (string|object|null) $responseFormat Resolved on every call, so a later asStructured() is honoured.
     */
    public function __construct(private readonly \Closure $responseFormat)
    {
    }

    /**
     * Set `response_format` unless the caller already passed one.
     *
     * @param Input $input
     * @return void
     */
    public function processInput(Input $input): void
    {
        $responseFormat = ($this->responseFormat)();
        $options = $input->getOptions();

        if (null === $responseFormat || isset($options['response_format'])) {
            return;
        }

        $options['response_format'] = $responseFormat;
        $input->setOptions($options);
    }
}
