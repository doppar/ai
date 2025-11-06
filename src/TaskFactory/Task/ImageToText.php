<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class ImageToText implements TaskInterface
{
    const TASK = TaskEnum::IMAGE_TO_TEXT;

    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/vit-gpt2-image-captioning';
        }
        $classifier = pipeline(self::TASK->value, $datas['model']);

        return $classifier($datas['imageUrl'], maxNewTokens: $datas['maxNewTokens']);
    }
}
