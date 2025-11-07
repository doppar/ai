<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class TextGeneration implements TaskInterface
{
    /**
     * The type of task this class represents.
     */
    const TASK = TaskEnum::TEXT_GENERATION;

    /**
     * Execute the text generation pipeline.
     *
     * @param mixed $datas Input parameters for the pipeline.
     *                     Expected structure:
     *                     [
     *                         'messages' => array,         // Array of chat messages or prompts
     *                         'model' => ?string,          // Optional: custom model name
     *                         'maxNewTokens' => int,       // Maximum tokens to generate
     *                         'returnFullText' => bool     // Whether to return full text or only new text
     *                     ]
     *
     * @return mixed Returns the generated text output from the model.
     * @throws \Exception
     */
    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/TinyLlama-1.1B-Chat-v1.0';
        }

        $generator = pipeline(self::TASK->value, modelName: $datas['model']);

        if (empty($datas['messages'])) {
            throw new \Exception('No messages provided');
        }

        $input = $generator->tokenizer->applyChatTemplate($datas['messages'], addGenerationPrompt: true, tokenize: false);

        return $generator($input, maxNewTokens: $datas['maxNewTokens'], returnFullText: $datas['returnFullText']);
    }
}
