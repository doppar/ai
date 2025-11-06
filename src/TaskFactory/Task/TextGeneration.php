<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class TextGeneration implements TaskInterface
{
    const TASK = TaskEnum::TEXT_GENERATION;
    
    public function execute(mixed $datas): mixed
    {
        if(empty($datas['model'])) {
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
