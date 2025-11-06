<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class SentimentAnalysis implements TaskInterface
{
    const TASK = TaskEnum::SENTIMENT_ANALYSIS;
    
    public function execute(mixed $datas): mixed
    {
        if($datas['data'] === null) {
            throw new \Exception('No data provided');
        }
        $classifier = pipeline(self::TASK->value, quantized: $datas['quantized'], modelName: $datas['model']);
        
        return $classifier($datas['data']);
    }
}
