<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class ImageClassification implements TaskInterface
{
    /**
     * The type of task this class represents.
     */
    const TASK = TaskEnum::IMAGE_CLASSIFICATION;

    /**
     * Execute the image classification pipeline.
     *
     * @param mixed $datas Input parameters for the pipeline.
     *                     Expected structure:
     *                     [
     *                         'imageUrl' => string,      // Path or URL to the image file
     *                         'model' => ?string,         // Optional: custom model name
     *                         'topK' => int               // Number of top predictions to return
     *                     ]
     *
     * @return mixed Returns an array of classification results with labels and scores.
     * @throws \Exception
     */
    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/vit-base-patch16-224';
        }

        if (empty($datas['imageUrl'])) {
            throw new \Exception('No image URL provided for image classification');
        }

        $classifier = pipeline(self::TASK->value, $datas['model']);

        return $classifier($datas['imageUrl'], topK: $datas['topK']);
    }
}
