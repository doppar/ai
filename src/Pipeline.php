<?php

namespace Doppar\Transformer;

use Doppar\Transformer\Enum\TaskEnum;
use function Codewithkyrian\Transformers\Pipelines\pipeline;

class Pipeline
{
    public static function execute(
        TaskEnum $task,
        ?string $data = null,
        ?string $model = null,
        bool $quantized = true,
        array $messages = [],
        bool $addGenerationPrompt = false,
        bool $tokenize = true,
        int $maxNewTokens = 256,
        bool $returnFullText = false,
    ): mixed
    {
        if ($task === TaskEnum::TEXT_GENERATION) {
            if(empty($model)) {
                $model = 'Xenova/TinyLlama-1.1B-Chat-v1.0';
            }
            $generator = pipeline($task->value, modelName: $model);
            if (!empty($messages)) {

                $input = $generator->tokenizer->applyChatTemplate($messages, addGenerationPrompt: true, tokenize: false);

                $output = $generator($input, maxNewTokens: 256, returnFullText: false);

                return $output;
            }

            throw new \Exception('No messages provided');
        } else {
            if($data === null) {
                throw new \Exception('No data provided');
            }
            $classifier = pipeline($task->value, quantized: $quantized, modelName: $model);
        }

        return $classifier($data);
    }
}