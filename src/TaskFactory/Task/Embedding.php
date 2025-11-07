<?php

namespace Doppar\AI\TaskFactory\Task;

use Doppar\AI\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class Embedding implements TaskInterface
{
    /**
     * The type of task this class represents.
     */
    const TASK = TaskEnum::EMBEDDING;

    /**
     * Execute the embedding pipeline.
     *
     * @param mixed $datas  Input data and parameters.
     *                      Expected structure:
     *                      [
     *                          'data' => string|array,     // The text(s) to embed
     *                          'model' => ?string,          // Optional: custom model name
     *                      ]
     *
     * @return mixed Returns the embedding result (usually a numeric vector or array of vectors)
     */
    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/all-MiniLM-L6-v2';
        }

        $embedder = pipeline(self::TASK->value, $datas['model']);

        return $embedder($datas['data']);
    }
}
