<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class ObjectDetection implements TaskInterface
{
    /**
     * The type of task this class represents.
     */
    const TASK = TaskEnum::OBJECT_DETECTION;

    /**
     * Execute the object detection pipeline.
     *
     * @param mixed $datas Input parameters for the pipeline.
     *                     Expected structure:
     *                     [
     *                         'imageUrl' => string,       // Path or URL to the image
     *                         'model' => ?string,          // Optional: custom model name
     *                         'threshold' => float         // Confidence threshold for detections
     *                     ]
     *
     * @return mixed Returns an array of detected objects with labels, scores, and bounding boxes.
     */
    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/detr-resnet-50';
        }

        $detector = pipeline(self::TASK->value, $datas['model']);

        return $detector($datas['imageUrl'], threshold: $datas['threshold']);
    }
}
