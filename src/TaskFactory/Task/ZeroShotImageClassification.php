<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class ZeroShotImageClassification implements TaskInterface
{
    /**
     * The type of task this class represents.
     */
    const TASK = TaskEnum::ZERO_SHOT_IMAGE_CLASSIFICATION;

    /**
     * Execute the zero-shot image classification pipeline.
     *
     * @param mixed $datas Input parameters for the pipeline.
     *                     Expected structure:
     *                     [
     *                         'imageUrl' => string,          // Path or URL to the image
     *                         'model' => ?string,            // Optional: model name
     *                         'candidateLabels' => array     // Candidate labels for classification
     *                     ]
     *
     * @return mixed Returns classification results with labels and confidence scores.
     * @throws \Exception
     */
    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/clip-vit-base-patch32';
        }

        if (empty($datas['candidateLabels'])) {
            throw new \Exception('No candidate labels provided');
        }

        $classifier = pipeline(self::TASK->value, $datas['model']);

        return $classifier($datas['imageUrl'], $datas['candidateLabels']);
    }
}
