<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class TextClassification implements TaskInterface
{
    const TASK = TaskEnum::TEXT_CLASSIFICATION;

    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/distilbert-base-uncased-finetuned-sst-2-english';
        }
        $textClassifier = pipeline(self::TASK->value, $datas['model']);

        return $textClassifier($datas['data']);
    }
}
