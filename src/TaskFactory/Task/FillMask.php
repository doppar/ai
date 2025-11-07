<?php

namespace Doppar\AI\TaskFactory\Task;

use Doppar\AI\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class FillMask implements TaskInterface
{
    /**
     * The type of task this class represents.
     */
    const TASK = TaskEnum::FILL_MASK;

    /**
     * Execute the fill-mask pipeline.
     *
     * @param mixed $datas Input parameters for the pipeline.
     *                     Expected structure:
     *                     [
     *                         'data' => string,         // Sentence containing a [MASK] token
     *                         'model' => ?string,        // Optional: specific model name
     *                         'topK' => int              // Number of top predictions to return
     *                     ]
     *
     * @return mixed Returns an array of predictions with their scores and token values.
     */
    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/bert-base-uncased';
        }
        $unmasker = pipeline(self::TASK->value, $datas['model']);

        return $unmasker($datas['data'], topK: $datas['topK']);
    }
}
