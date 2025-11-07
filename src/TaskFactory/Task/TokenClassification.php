<?php

namespace Doppar\AI\TaskFactory\Task;

use Doppar\AI\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class TokenClassification implements TaskInterface
{
    /**
     * The type of task this class represents.
     */
    const TASK = TaskEnum::TOKEN_CLASSIFICATION;

    /**
     * Execute the token classification pipeline.
     *
     * @param mixed $datas Input parameters for the pipeline.
     *                     Expected structure:
     *                     [
     *                         'data' => string,           // Text to classify at token level
     *                         'model' => ?string          // Optional: custom model name
     *                     ]
     *
     * @return mixed Returns the token-level classification results,
     */
    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/bert-base-NER';
        }

        $classifier = pipeline(self::TASK->value, $datas['model']);

        return $classifier($datas['data']);
    }
}
