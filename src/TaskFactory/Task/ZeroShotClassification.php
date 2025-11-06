<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class ZeroShotClassification implements TaskInterface
{
    const TASK = TaskEnum::ZERO_SHOT_CLASSIFICATION;
    
    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/distilbert-base-uncased-mnli';
        }
        if (empty($datas['candidateLabels'])) {
            throw new \Exception('No candidate labels provided');
        }
        $classifier = pipeline(self::TASK->value, $datas['model']);        
        return $classifier($datas['data'], $datas['candidateLabels']);
    }
}
