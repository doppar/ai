<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class QuestionAnswer implements TaskInterface
{
    const TASK = TaskEnum::QUESTION_ANSWERING;
    
    public function execute(mixed $datas): mixed
    {
        if($datas['question'] === null || $datas['context'] === null) {
            throw new \Exception('No question or context provided');
        }
        $questionAnswerer = pipeline(self::TASK->value, $datas['model']);
        
        return $questionAnswerer($datas['question'], $datas['context'], topK: $datas['topK']);
    }
}
