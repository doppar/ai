<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class ZeroShotImageClassification implements TaskInterface
{
    const TASK = TaskEnum::ZERO_SHOT_IMAGE_CLASSIFICATION;

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
