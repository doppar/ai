<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class FillMask implements TaskInterface
{
    const TASK = TaskEnum::FILL_MASK;
    
    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/bert-base-uncased';
        }
        $unmasker = pipeline(self::TASK->value, $datas['model']);
        
        return $unmasker($datas['data'], topK: $datas['topK']);
    }
}
