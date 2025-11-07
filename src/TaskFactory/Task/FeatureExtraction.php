<?php

namespace Doppar\AI\TaskFactory\Task;

use Doppar\AI\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class FeatureExtraction implements TaskInterface
{
    /**
     * The type of task this class represents.
     */
    const TASK = TaskEnum::FEATURE_EXTRACTION;

    /**
     * Execute the feature extraction pipeline.
     *
     * @param mixed $datas Input data and parameters.
     *                     Expected structure:
     *                     [
     *                         'data' => string|array,     // Input text(s) to process
     *                         'model' => ?string,          // Optional: custom model name
     *                     ]
     *
     * @return mixed Returns extracted features (typically arrays or tensors)
     */
    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/all-MiniLM-L6-v2';
        }

        $extractor = pipeline(self::TASK->value, $datas['model']);

        return $extractor($datas['data']);
    }
}
