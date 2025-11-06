<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class Summarization implements TaskInterface
{
    const TASK = TaskEnum::SUMMARIZATION;

    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/distilbart-cnn-6-6';
        }
        $summarizer = pipeline(self::TASK->value, $datas['model']);

        return $summarizer($datas['data'], maxNewTokens: $datas['maxNewTokens']);
    }
}
