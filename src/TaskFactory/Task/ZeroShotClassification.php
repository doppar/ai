<?php

namespace Doppar\AI\TaskFactory\Task;

use Doppar\AI\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class ZeroShotClassification implements TaskInterface
{
    /**
     * The type of task this class represents.
     */
    const TASK = TaskEnum::ZERO_SHOT_CLASSIFICATION;

    /**
     * Execute the zero-shot classification pipeline.
     *
     * @param mixed $datas Input parameters for the pipeline.
     *                     Expected structure:
     *                     [
     *                         'data' => string,               // Text to classify
     *                         'model' => ?string,             // Optional: model name
     *                         'candidateLabels' => array      // Candidate labels for classification
     *                     ]
     *
     * @return mixed Returns classification results with labels and confidence scores.
     * @throws \Exception
     */
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
