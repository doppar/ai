<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class TokenClassification implements TaskInterface
{
    const TASK = TaskEnum::TOKEN_CLASSIFICATION;

    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/bert-base-NER';
        }
        $classifier = pipeline(self::TASK->value, $datas['model']);

        return $classifier($datas['data']);
    }
}
