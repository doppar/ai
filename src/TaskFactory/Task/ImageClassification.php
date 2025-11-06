<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class ImageClassification implements TaskInterface
{
    const TASK = TaskEnum::IMAGE_CLASSIFICATION;

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
