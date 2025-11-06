<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class FeatureExtraction implements TaskInterface
{
    const TASK = TaskEnum::FEATURE_EXTRACTION;

    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/all-MiniLM-L6-v2';
        }
        $extractor = pipeline(self::TASK->value, $datas['model']);

        return $extractor($datas['data']);
    }
}
