<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class TextClassification implements TaskInterface
{
    /**
     * The type of task this class represents.
     */
    const TASK = TaskEnum::TEXT_CLASSIFICATION;

    /**
     * Execute the text classification pipeline.
     *
     * @param mixed $datas Input parameters for the pipeline.
     *                     Expected structure:
     *                     [
     *                         'data' => string,           // Text to classify
     *                         'model' => ?string          // Optional: custom model name
     *                     ]
     *
     * @return mixed Returns the classification result, typically labels with scores.
     */
    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/distilbert-base-uncased-finetuned-sst-2-english';
        }

        $textClassifier = pipeline(self::TASK->value, $datas['model']);

        return $textClassifier($datas['data']);
    }
}
